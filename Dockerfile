# syntax=docker/dockerfile:1

# --- Build stage (not strictly needed for PHP, but for extensibility and best practice) ---
FROM php:8.2-apache AS base

# Install system dependencies and PHP extensions in a single layer
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for image handling and MySQL
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd pdo_mysql mysqli

# Enable Apache mod_rewrite (useful for pretty URLs)
RUN a2enmod rewrite

# --- Final stage ---
FROM php:8.2-apache AS final

# Copy PHP extensions and configuration from build stage
COPY --from=base /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=base /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=base /etc/apache2/mods-enabled/rewrite.load /etc/apache2/mods-enabled/rewrite.load

# Set up a non-root user for security
RUN useradd -m snapcatuser && \
    chown -R snapcatuser:www-data /var/www/html

# Copy application code (excluding .git and other ignored files via .dockerignore)
COPY --link . /var/www/html/

# Ensure uploads directory exists and is writable by Apache and the app user
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/uploads

# Switch to the non-root user
USER snapcatuser

# Expose HTTP port
EXPOSE 80

# Use the default Apache entrypoint and command
