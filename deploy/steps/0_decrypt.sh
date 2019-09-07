#!/bin/bash
while getopts ":b:" opt; do
  case ${opt} in
  b)
    BRANCH_NAME=${OPTARG}
    ;;
  \?)
    echo "Invalid option: -$OPTARG"
    ;;
  esac
done

function decrypt() {
  gcloud kms decrypt \
    --location global \
    --keyring mathrix-education-keyring \
    --key "${2}" \
    --ciphertext-file "${1}.enc" \
    --plaintext-file "${1}"
  echo "Decrypted: ${1}"
}

decrypt deploy/secrets/github-oauth.github.com mathrix-education-api-cloudbuild
decrypt "environments/${BRANCH_NAME}/.env" "mathrix-education-api-${BRANCH_NAME}"
decrypt "environments/${BRANCH_NAME}/jwt_auth.json" "mathrix-education-api-${BRANCH_NAME}"
mv "environments/${BRANCH_NAME}/jwt_auth.json" storage/keychain/jwt_auth.json
