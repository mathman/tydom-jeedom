// Required when testing against a local Tydom hardware
// to fix "self signed certificate" errors
process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

const {createClient} = require('tydom-client');
var express = require('express');
var npid = require('npid');

const args = process.argv.slice(2);

const host = args[0];
const username = args[1];
const password = args[2];
const isremote = args[3];

hostname = '';
if (isremote == 1) {
    hostname = 'mediation.tydom.com';
}
else {
    hostname = host;
}

try {
    var pid = npid.create(args[4]);
    pid.removeOnExit();
} catch (err) {
    console.log(err);
    process.exit(1);
}

const client = createClient({username, password, hostname});

(async () => {
    console.log(`Connecting to "${hostname}"...`);
    const socket = await client.connect();

    var app = express();

    app.get('/info', async function(req, res) {

        const info = await client.get('/info');
        res.setHeader('Content-Type', 'application/json');
        res.end(JSON.stringify(info));
    })
    .get('/devices/data', async function(req, res) {

        const devices = await client.get('/devices/data');
        res.setHeader('Content-Type', 'application/json');
        res.end(JSON.stringify(devices));
    })
    .get('/configs/file', async function(req, res) {

        const configs = await client.get('/configs/file');
        res.setHeader('Content-Type', 'application/json');
        res.end(JSON.stringify(configs));
    })
    .get('/moments/file', async function(req, res) {

        const moments = await client.get('/moments/file');
        res.setHeader('Content-Type', 'application/json');
        res.end(JSON.stringify(moments));
    })
    .get('/scenarios/file', async function(req, res) {

        const scenarios = await client.get('/scenarios/file');
        res.setHeader('Content-Type', 'application/json');
        res.end(JSON.stringify(scenarios));
    })
    .get('/protocols', async function(req, res) {

        const protocols = await client.get('/protocols');
        res.setHeader('Content-Type', 'application/json');
        res.end(JSON.stringify(protocols));
    })
    .get('/device/:decivenum/endpoints', async function(req, res) {
        const info = await client.get('/devices/' + req.params.decivenum + '/endpoints/' + req.params.decivenum + '/data');
        res.setHeader('Content-Type', 'application/json');
        res.end(JSON.stringify(info));
    })
    .get('/stop', function(req, res) {
        process.exit(0);
    })
    .use(function(req, res, next){
        res.setHeader('Content-Type', 'text/plain');
        res.status(404).send('Page introuvable !');
    });

    app.listen(8080);
})();