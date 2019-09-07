for f in $(ls ${KEYCHAIN_DIR}/*.enc); do
  PLAINTEXT_FILE=${KEYCHAIN_DIR}/$(basename ${KEYCHAIN_DIR}/${f} .enc)
  CIPHERTEXT_FILE=${PLAINTEXT_FILE}.enc

  gcloud kms decrypt \
    --location global \
    --keyring mathrix-drive-keyring \
    --key drive-api-key \
    --plaintext-file ${PLAINTEXT_FILE} \
    --ciphertext-file ${CIPHERTEXT_FILE}

   echo "Decrypted: ${CIPHERTEXT_FILE} => ${PLAINTEXT_FILE}"
done;
