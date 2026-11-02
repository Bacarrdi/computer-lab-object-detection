# Imagen base con PHP 8.3
FROM php:8.3-cli-bookworm

# Obtener Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Configuración de subida de imágenes
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Instalar Python y herramientas necesarias
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        python3 \
        python3-pip \
        python3-venv \
        git \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar y habilitar la extensión Parle
RUN pecl install parle-0.8.5 \
    && docker-php-ext-enable parle

# Copiar las dependencias de Python
COPY requirements.txt /tmp/requirements.txt

# Crear un entorno virtual para Python
RUN python3 -m venv /opt/venv

# Actualizar las herramientas de instalación de Python
RUN /opt/venv/bin/python -m pip install --upgrade \
    pip setuptools wheel

# Instalar PyTorch exclusivamente para CPU
# Permitir resolver dependencias desde PyPI
RUN /opt/venv/bin/python -m pip install \
    torch==2.8.0+cpu torchvision==0.23.0+cpu \
    --index-url https://download.pytorch.org/whl/cpu \
    --extra-index-url https://pypi.org/simple

# Instalar las bibliotecas del proyecto
RUN /opt/venv/bin/pip install --no-cache-dir \
    -r /tmp/requirements.txt

# Utilizar el Python del entorno virtual por defecto
ENV PATH="/opt/venv/bin:${PATH}"

# Directorio de trabajo dentro del contenedor
WORKDIR /var/www/html