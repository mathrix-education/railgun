#!/usr/bin/env bash
DATE=$(date +%F_%H-%M-%S)
DB_SOURCE=education_production
DB_DEST=education_edge

read -p "You are going copy the database $DB_SOURCE to $DB_DEST, continue? [Y/n]" -n 1 -r

if [[ $REPLY != "" && ! $REPLY =~ ^[Yy]$ ]]; then
    exit 1
fi

SERVICE_ACCOUNT="p731492094632-9gcerm@gcp-sa-cloud-sql.iam.gserviceaccount.com"
BUCKET="gs://mathrix-backups"
OBJECT_SOURCE="$BUCKET/$DB_SOURCE/$DB_SOURCE-$DATE.sql.gz"
OBJECT_DEST="$BUCKET/$DB_DEST/$DB_DEST-$DATE.sql.gz"

gcloud sql export sql mysql "$OBJECT_SOURCE" --database=$DB_SOURCE

gsutil cp "$OBJECT_SOURCE" - |
    gzip -d |
    pv |
    sed "s/$DB_SOURCE/$DB_DEST/g" |
    gzip |
    gsutil cp - "$OBJECT_DEST"

gsutil acl ch -u "$SERVICE_ACCOUNT:W" "$BUCKET"
gsutil acl ch -u "$SERVICE_ACCOUNT:R" "$OBJECT_DEST"
gcloud sql import sql mysql "$OBJECT_DEST" "--database=$DB_DEST" --quiet
gsutil acl ch -d "$SERVICE_ACCOUNT" "$OBJECT_DEST"
