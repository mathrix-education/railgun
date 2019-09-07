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

SQL_INSTANCE=mathrix-education:europe-west1:mysql
SERVICE_ACCOUNT=education-api-${BRANCH_NAME}@mathrix-education.iam.gserviceaccount.com

gcloud beta run deploy education-api-"${BRANCH_NAME}" \
    --service-account="${SERVICE_ACCOUNT}" \
    --image eu.gcr.io/mathrix-education/education-api:"${BRANCH_NAME}-${SHORT_SHA}" \
    --platform=managed \
    --region=europe-west1 \
    --allow-unauthenticated \
    --set-cloudsql-instances=${SQL_INSTANCE}
