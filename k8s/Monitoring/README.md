# Monitoring stack

This directory deploys Grafana, Prometheus, Loki, and Promtail into the
`monitoring` namespace. The Grafana Deployment reads its administrator
credentials from the `grafana-secret` Kubernetes Secret. No real credential is
stored in this repository.

## CI/CD deployment (recommended)

Add these encrypted GitHub Actions repository secrets:

- `GRAFANA_ADMIN_USER`
- `GRAFANA_ADMIN_PASSWORD` (use a unique randomly generated value of at least
  16 characters)
- `DISCORD_WEBHOOK` (your teammate's webhook for CI/CD pipeline notifications)
- `GRAFANA_DISCORD_WEBHOOK` (a separate webhook that targets the
  `monitoring-alerts` channel for Grafana runtime alerts)

The deployment workflow creates or updates `grafana-secret` and
`grafana-alerting-secret` with `kubectl` before applying the manifests. Secret
values are passed as environment variables and are not written to a checked-in
YAML file.

Rotate the password by changing `GRAFANA_ADMIN_PASSWORD` in GitHub and rerunning
the workflow. The workflow restarts Grafana and runs Grafana's supported admin
password reset command, so rotation works for both a new database and an
existing persistent Grafana database.

## Local/demo deployment

Create the namespace and Secret without writing the password to disk:

```bash
kubectl apply -f k8s/Monitoring/namespace.yaml
read -rsp 'Grafana admin password: ' GRAFANA_ADMIN_PASSWORD
echo
read -rsp 'Grafana Discord webhook URL: ' GRAFANA_DISCORD_WEBHOOK_URL
echo
kubectl -n monitoring create secret generic grafana-secret \
  --from-literal=admin-user='admin' \
  --from-literal=admin-password="$GRAFANA_ADMIN_PASSWORD" \
  --dry-run=client -o yaml | kubectl apply -f -
kubectl -n monitoring create secret generic grafana-alerting-secret \
  --from-literal=discord-webhook-url="$GRAFANA_DISCORD_WEBHOOK_URL" \
  --dry-run=client -o yaml | kubectl apply -f -
unset GRAFANA_ADMIN_PASSWORD GRAFANA_DISCORD_WEBHOOK_URL
kubectl apply -f k8s/ --recursive
```

Do not use a real password in a manifest, shell history, screenshot, report, or
Git commit. For a production GKE environment, Google Secret Manager with the
Secrets Store CSI Driver or External Secrets Operator is preferable because it
supports centralized access control and rotation. That integration requires
cluster add-ons and IAM configuration, so this repository uses the existing
GitHub Actions deployment path.

## Security controls

- Monitoring workloads run with privilege escalation disabled, all Linux
  capabilities dropped, and the RuntimeDefault seccomp profile.
- Grafana and Loki do not receive Kubernetes API tokens because they do not
  need them.
- Provisioning ConfigMaps are mounted read-only.
- Promtail's cluster-wide RBAC is limited to read-only Pod discovery.
- Grafana account sign-up, anonymous access, Gravatar, analytics reporting, and
  update checks are disabled.
- Existing resource limits, health probes, persistent storage, data sources,
  log collection, and dashboard provisioning remain enabled.

## Optional enhancement: proactive Discord runtime alerts

Grafana provisions a Discord contact point and two Grafana-managed alert rules
from `grafana-alerting.yaml`:

- **PHP application unavailable (critical):** fires when `apache_up` is below
  `1` for one minute. Missing data also fires because it can mean the exporter
  or application target has disappeared.
- **Sustained high PHP CPU (warning):** fires when any PHP Pod exceeds 80% of
  its configured `200m` CPU request for five minutes. Missing CPU data does not
  fire because application availability is covered by the first rule.

Resolved notifications are enabled, so Discord receives a follow-up when the
application or CPU usage returns to normal. The alert configuration is mounted
read-only and the webhook URL exists only in `grafana-alerting-secret`.
The alerts use a dedicated read-only `Prometheus Alerting` data source with a
fixed UID, leaving the existing dashboard data source unchanged.

Keep `DISCORD_WEBHOOK` and `GRAFANA_DISCORD_WEBHOOK` separate. Changing the
Grafana destination requires updating only `GRAFANA_DISCORD_WEBHOOK` in
**GitHub > Settings > Secrets and variables > Actions** and rerunning the
workflow. The workflow updates `grafana-alerting-secret` and restarts Grafana,
which reloads the contact point with the new webhook.

## Optional enhancement: CNAS runtime dashboard

`grafana-runtime-dashboard.yaml` provisions **CNAS Runtime Monitoring** under
the **CNAS** Grafana folder. It contains four panels:

- PHP Pod CPU as a percentage of the configured `200m` request, including the
  same 80% threshold used by the high-CPU alert.
- PHP Pod working-set memory as a percentage of the configured `256Mi` request.
- Kubernetes node CPU usage from cAdvisor.
- Current PHP application availability from `apache_up{job="php-app"}`.

The dashboard uses the fixed `prometheus-alerts` data source UID and refreshes
every 30 seconds. It is provisioned read-only so the repository remains the
source of truth.

### Validation and demonstration

After deployment, confirm that Grafana loaded the resources:

```bash
kubectl -n monitoring logs deployment/grafana | grep -i provisioning
kubectl -n monitoring get secret grafana-alerting-secret
```

In Grafana, open **Alerting > Alert rules** and confirm both rules appear under
`CNAS Runtime Alerts`. The Discord contact point is file-provisioned, so Grafana
10.4.3 deliberately makes it read-only and does not show the UI **Test** button.

To send a safe test through Grafana without stopping the application, start a
one-off Job from the suspended `grafana-alerting-test` template:

```bash
TEST_JOB="grafana-alert-test-$(date +%s)"
kubectl -n monitoring create job --from=cronjob/grafana-alerting-test "$TEST_JOB"
kubectl -n monitoring wait --for=condition=complete "job/$TEST_JOB" --timeout=120s
kubectl -n monitoring logs "job/$TEST_JOB"
kubectl -n monitoring delete job "$TEST_JOB"
```

The logs should say `Grafana Discord test notification sent successfully`, and
Discord should receive a `TestAlert` notification. The final command removes the
completed test Job. The webhook and Grafana password are read from Kubernetes
Secrets and are not printed.

To demonstrate a real availability alert and recovery:

```bash
# Temporarily remove the HPA so it does not immediately restore three replicas.
kubectl -n default delete hpa php-app-hpa
kubectl -n default scale deployment/php-app --replicas=0
# Wait about two minutes for evaluation, pending time, and notification grouping.
kubectl -n default scale deployment/php-app --replicas=3
kubectl apply -f k8s/hpa.yaml
```

Discord should receive a firing notification followed by a resolved
notification. The final command restores the HPA from its version-controlled
manifest after the controlled availability test.

To demonstrate the high-CPU alert, run a bounded PHP CPU loop in one application
Pod. It stops automatically after six and a half minutes:

```bash
PHP_POD=$(kubectl -n default get pod -l app=php-app \
  -o jsonpath='{.items[0].metadata.name}')
kubectl -n default exec "$PHP_POD" -c php -- \
  php -r '$end=microtime(true)+390; while (microtime(true)<$end) {}'
```

The rule deliberately has a five-minute pending period to avoid alerts for
brief spikes. Discord should receive a firing notification and then a resolved
notification after the bounded load ends.
