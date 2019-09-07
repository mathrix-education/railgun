#!/bin/bash
while getopts ":b:s:" opt; do
  case ${opt} in
  b)
    BRANCH_NAME=${OPTARG}
    ;;
  s)
    SHORT_SHA=${OPTARG}
    ;;
  \?)
    echo "Invalid option: -$OPTARG"
    ;;
  esac
done

gcloud beta run deploy education-api-"${BRANCH_NAME}" \
  --image eu.gcr.io/mathrix-education/education-api:"${BRANCH_NAME}-${SHORT_SHA}" \
  --platform=managed \
  --region=europe-west1 \
  --allow-unauthenticated \
  --set-env-vars="APP_ENV=${BRANCH_NAME},SHORT_SHA=${SHORT_SHA}"

# Required for the first deployment
#SQL_INSTANCE=mathrix-education:europe-west1:mysql
#SERVICE_ACCOUNT=education-api-${BRANCH_NAME}@mathrix-education.iam.gserviceaccount.com
# --service-account="${SERVICE_ACCOUNT}" \
# --set-cloudsql-instances=${SQL_INSTANCE}
