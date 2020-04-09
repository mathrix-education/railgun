#!/usr/bin/env bash
# Default options
DB_USER="root"
DB_PASSWORD="salome3004"
DB_SOURCE="mathrix_education_local"
DB_TARGET="mathrix_education_v7"

while getopts ":u:p:s:t:" opt; do
    case ${opt} in
    u)
        DB_USER="$OPTARG"
        ;;
    p)
        DB_PASSWORD="$OPTARG"
        ;;
    s)
        DB_SOURCE="$OPTARG"
        ;;
    t)
        DB_TARGET="$OPTARG"
        ;;
    \?)
        ;;
    esac
done

DATE=$(date +%F_%H-%M-%S)
DB_SERVICE_ACCOUNT="p731492094632-9gcerm@gcp-sa-cloud-sql.iam.gserviceaccount.com"
BUCKET="gs://mathrix-backups"
OBJECT="$BUCKET/databases/$DB_TARGET/$DB_TARGET-$DATE.sql.gz"

mysqldump \
    -h 127.0.0.1 \
    --port=3306 \
    --user="$DB_USER" \
    --password="$DB_PASSWORD" \
    --databases "$DB_SOURCE" \
    --hex-blob \
    --triggers \
    --order-by-primary \
    --no-autocommit \
    --default-character-set=utf8mb4 \
    --single-transaction \
    --column-statistics=0 \
    | sed "s/$DB_SOURCE/$DB_TARGET/g" \
    | gzip \
    | pv \
    | gsutil cp - "$OBJECT"

gsutil acl ch -u "$DB_SERVICE_ACCOUNT:W" "$BUCKET"
gsutil acl ch -u "$DB_SERVICE_ACCOUNT:R" "$OBJECT"
gcloud sql import sql mysql "$OBJECT" "--database=$DB_TARGET" --quiet
gsutil acl ch -d "$DB_SERVICE_ACCOUNT" "$BUCKET"
gsutil acl ch -d "$DB_SERVICE_ACCOUNT" "$OBJECT"
