FROM nginx:stable-alpine

# Install openssl for certificate generation
RUN apk add --no-cache openssl

# Copy custom entrypoint script
COPY docker/nginx/entrypoint.sh /custom-entrypoint.sh
RUN chmod +x /custom-entrypoint.sh

ENTRYPOINT ["/custom-entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
