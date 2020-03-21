#!/usr/bin/env bash
TARGET_DB=${1:-mathrix_education_local}
DESTINATION_FILE=storage/artifacts/mathrix_education_v7.sql
BACKUP_FILE=$(gsutil ls gs://mathrix-backups/databases/mathrix_education_master/ | tail -1)

# Download the backup from Google Cloud Storage
gsutil cp "${BACKUP_FILE}" "${DESTINATION_FILE}.gz"
rm -f "${DESTINATION_FILE}"
gzip -d "${DESTINATION_FILE}.gz"

# Patch the database name
sed -i "s/mathrix_education_master/${TARGET_DB}/g" "${DESTINATION_FILE}"

ls -l "${DESTINATION_FILE}"
