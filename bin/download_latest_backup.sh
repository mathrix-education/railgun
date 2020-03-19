#!/usr/bin/env bash
TARGET=${1:mathrix_education_testing}
DESTINATION_FILE=storage/artifacts/mathrix_education_v7.sql
BACKUP_FILE=$(gsutil ls gs://mathrix-backups/databases/mathrix_education_master/ | tail -1)
gsutil cp "${BACKUP_FILE}" "${DESTINATION_FILE}.gz"
gzip -d "${DESTINATION_FILE}.gz"
sed -i "s/mathrix_education_master/${TARGET}/g" "${DESTINATION_FILE}"
