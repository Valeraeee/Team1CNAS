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

The deployment workflow creates or updates `grafana-secret` with `kubectl`
before applying the manifests. Secret values are passed as environment
variables and are not written to a checked-in YAML file.

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
kubectl -n monitoring create secret generic grafana-secret \
  --from-literal=admin-user='admin' \
  --from-literal=admin-password="$GRAFANA_ADMIN_PASSWORD" \
  --dry-run=client -o yaml | kubectl apply -f -
unset GRAFANA_ADMIN_PASSWORD
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
