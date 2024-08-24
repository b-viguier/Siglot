FROM ruby:3.3.4

# Install Bundler & Jekyll
RUN set -eux; gem install jekyll bundler

# Clean up
WORKDIR /srv/jekyll

EXPOSE 4000
EXPOSE 35729

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends bash

COPY docs/Gemfile ./
RUN set -eux; bundle install
