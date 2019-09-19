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

ENV_VARS=$(./deploy/utils/envcat.sh "./environments/${BRANCH_NAME}/.env")

gcloud beta run deploy education-api-"${BRANCH_NAME}" \
  --image eu.gcr.io/mathrix-education/education-api:"${BRANCH_NAME}-${SHORT_SHA}" \
  --platform=managed \
  --region=europe-west1 \
  --allow-unauthenticated \
  --set-env-vars="${ENV_VARS}"
