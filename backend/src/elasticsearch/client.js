const fs = require("fs");
const { Client } = require("@elastic/elasticsearch");
const client = new Client({
  node: `https://${process.env.ELASTICSEARCH_HOST}:${process.env.ELASTICSEARCH_PORT}`,
  auth: {
    username: process.env.ELASTICSEARCH_CLIENT,
    password: process.env.ELASTICSEARCH_PASSWORD,
  },
  tls: {
    ca: fs.readFileSync("./certs/ca/ca.crt"),
    rejectUnauthorized: false,
  },
});

client
  .ping()
  .then((response) => console.log("Connected to Elasticsearch"))
  .catch((error) => console.log(error));

module.exports = client;
