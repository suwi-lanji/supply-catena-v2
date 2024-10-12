sudo docker cp $(sudo docker ps -q):/var/www/html/database/database.sqlite ./database/
sudo docker cp $(sudo docker ps -q):/var/www/html/storage/app/public/ ./storage/app/
sudo docker stop $(sudo docker ps -q)
sudo docker build -t warehouse .
sudo docker run -d -p 8089:8000 warehouse
