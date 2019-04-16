#!/bin/bash
WEB01=/var/www/master/api/current
PROJECT=/home/mathieu/Projects/mathrix/education-api

SQL_SERVICE_ACCOUNT=kta7sdbqmveq7fnv4em5tjhzee@speckle-umbrella-20.iam.gserviceaccount.com
BUCKET=cdn.mathrix.fr
#DATABASE_TARGET=master_mathrix_education
DATABASE_TARGET=dev_mathrix_education

#./maintenance_on.sh
ssh root@web01.mathrix.fr rm ${WEB01}/storage/backups/database/*
ssh root@web01.mathrix.fr php ${WEB01}/artisan backup:mysql-dump --no-compress
ssh root@web01.mathrix.fr mv ${WEB01}/storage/backups/database/* ${WEB01}/storage/backups/database/dump.sql
ssh root@web01.mathrix.fr gzip ${WEB01}/storage/backups/database/dump.sql
scp root@web01.mathrix.fr:/var/www/master/api/current/storage/backups/database/dump.sql.gz \
    ${PROJECT}/storage/framework/cache/dump.sql.gz
gzip -df ${PROJECT}/storage/framework/cache/dump.sql.gz

php ${PROJECT}/artisan db:destroy
pv ${PROJECT}/storage/framework/cache/dump.sql \
    | mysql -u'root' -p'salome3004' --host=127.0.0.1 --port=3306 --database="mathrix_fr_dev"

php ${PROJECT}/artisan pyramid
#
#mysqldump mathrix_fr_dev -u'root' -p'salome3004' --host=127.0.0.1 --port=3306 \
#    --result-file=${PROJECT}/storage/framework/cache/converted.sql
#
#gzip -f ${PROJECT}/storage/framework/cache/converted.sql
#
#gsutil cp ${PROJECT}/storage/framework/cache/converted.sql.gz gs://cdn.mathrix.fr/converted.sql
#gsutil acl ch -u ${SQL_SERVICE_ACCOUNT}:W gs://${BUCKET}
#gsutil acl ch -u ${SQL_SERVICE_ACCOUNT}:R gs://${BUCKET}/converted.sql
#gcloud sql import sql mysql gs://${BUCKET}/converted.sql --database=${DATABASE_TARGET}
#gsutil acl ch -d ${SQL_SERVICE_ACCOUNT} gs://${BUCKET}/converted.sql
#gsutil acl ch -d ${SQL_SERVICE_ACCOUNT} gs://${BUCKET}
