# DDEVのPHP 8.4を8.4.27へ更新する
# Created: 2026-10-22
# Last updated: 2026-10-22

ARG PHP84_PACKAGE_VERSION=8.4.27-1~deb13u1

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
        --only-upgrade \
        --no-install-recommends \
        -o Dpkg::Options::="--force-confold" \
        "php8.4-cli=${PHP84_PACKAGE_VERSION}" \
        "php8.4-fpm=${PHP84_PACKAGE_VERSION}" \
        "php8.4-common=${PHP84_PACKAGE_VERSION}" \
    && apt-get clean
