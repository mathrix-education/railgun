#!/usr/bin/env bash
# Default options
DB_TARGET="mathrix_education_local"

while getopts ":t:" opt; do
    case ${opt} in
    t)
        DB_TARGET="$OPTARG"
        ;;
    \?)
        ;;
    esac
done

DESTINATION_FILE=storage/buster/mathrix_education_v7.sql
BACKUP_FILE=$(gsutil ls gs://mathrix-backups/databases/mathrix_education_master/ | tail -1)

# Download the backup from Google Cloud Storage
gsutil cp "${BACKUP_FILE}" "${DESTINATION_FILE}.gz"

echo "Extracting "${DESTINATION_FILE}.gz""
rm -f "$DESTINATION_FILE"
gzip -d "$DESTINATION_FILE.gz"

strip() {
    echo "Removing $1 table"
    ls -l "$DESTINATION_FILE"
    START=$(($(grep -n "Table structure for table \`$1\`" "$DESTINATION_FILE" | cut -d: -f1) - 1))
    END=$(($(grep -n "ALTER TABLE \`$1\` ENABLE KEYS" "$DESTINATION_FILE" | cut -d: -f1) + 1))
    sed -i "${START},${END}d" "$DESTINATION_FILE"
}

strip "cache"
strip "settings"

echo "Patching the database name"
sed -i "s/mathrix_education_master/${DB_TARGET}/g" "$DESTINATION_FILE"
ls -l "$DESTINATION_FILE"

