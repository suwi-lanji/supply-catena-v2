# Use the official PostgreSQL image as the base image
FROM postgres:13.5

# Set environment variables
ENV POSTGRES_USER suwilanji
ENV POSTGRES_PASSWORD lordcaesar

# Expose the PostgreSQL port
EXPOSE 5432

# Use a volume for PostgreSQL data
VOLUME ["/var/lib/postgresql/data"]

# Default command to run PostgreSQL
CMD ["postgres"]
