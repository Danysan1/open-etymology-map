#!/bin/bash

if [ -n "$(which docker-compose)" ]; then
  echo "Docker Compose is already installed, init has already been done"
  exit 1
fi

if [ -d "open-etymology-map" ]; then
  echo "The open-etymology-map folder already exists, init has already been done"
  exit 2
fi

## https://docs.aws.amazon.com/AmazonECS/latest/developerguide/docker-basics.html#install_docker

sudo yum update -y
sudo yum install git -y
sudo amazon-linux-extras install docker -y
sudo systemctl enable docker
sudo systemctl start docker
sudo usermod -a -G docker ec2-user

## https://gist.github.com/npearce/6f3c7826c7499587f00957fee62f8ee9

sudo curl -L https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m) -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
docker-compose version

## https://gitlab.com/openetymologymap/open-etymology-map/-/blob/main/CONTRIBUTING.md

git clone --recurse-submodules https://gitlab.com/openetymologymap/open-etymology-map.git
cd open-etymology-map
cp ".env.example" "osm-wikidata-map-framework-oem/.env"
cp "promtail/config.template.yaml" "promtail/config.yaml"

# https://docs.docker.com/compose/profiles/#enable-profiles
export COMPOSE_PROFILES=prod,promtail
docker-compose pull
docker-compose up -d

## https://certbot.eff.org/instructions?ws=apache&os=debianbuster

docker-compose exec oem-web-prod certbot --apache
## Future certificate renewal: docker-compose exec oem-web-prod certbot renew

chmod u+x update.sh
echo '0 * * * * ./open-etymology-map/update.sh' | crontab -
## Logs: docker-compose logs

echo 'Remember to fill .env !'
echo 'Remember to fill promtail/config.yaml !'
