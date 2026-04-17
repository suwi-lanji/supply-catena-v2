#!/bin/bash
set -e

# --- Configuration ---
# Replace these with your actual project details.
PROJECT_ID="constellation-458907"
REGION="us-central1"
SERVICE_NAME="supplycatena"

# --- Do not edit below this line ---
IMAGE_NAME="${REGION}-docker.pkg.dev/${PROJECT_ID}/cloud-run-source-deploy/${SERVICE_NAME}:latest"
SETUP_JOB_NAME="${SERVICE_NAME}-setup-v3"

echo "--- Building Docker Image ---"
echo "IMAGE: ${IMAGE_NAME}"
gcloud builds submit --tag "${IMAGE_NAME}"

echo ""
echo "--- Deploying Web Service with Octane ---"
gcloud run deploy ${SERVICE_NAME} \
  --image="${IMAGE_NAME}" \
  --region ${REGION} \
  --allow-unauthenticated \
  --cpu-boost

echo ""
echo "--- Deployment Complete ---"
