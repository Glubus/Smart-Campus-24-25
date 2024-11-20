sudo git clone https://forge.iut-larochelle.fr/2024-2025-but-info2-a-sae34/k2/k23/sae-12-k-23-stack.git
cd sae*
sudo chmod 777 -R *
sudo docker compose up --build --remove-orphans --force-recreate -d
sleep 3;
sudo docker exec but-info2-a-sae3-sfapp composer install
