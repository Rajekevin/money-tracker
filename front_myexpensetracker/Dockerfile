FROM node:20

WORKDIR /app

COPY package*.json ./

RUN npm install -g vite \
 && npm install

COPY . .

CMD ["npm", "run", "build"]
