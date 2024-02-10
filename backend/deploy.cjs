// const { Client } = require('ssh2');
const fs = require('fs');

let Client = require('ssh2-sftp-client');
let sftp = new Client();

const connSettings = {
    host: '77.222.56.111',
    port: 22,
    username: '',
    // privateKey: fs.readFileSync('C:\Users\dimab\.ssh\id_rsa'),
    password: '',
    tryKeyboard: true
};

// const commands = [
//     'cd /path/to/remote/directory',
//     'rm -rf *', // Remove existing files (optional)
//     'scp /path/to/local/file1 user@host:/path/to/remote/directory', // Example SCP command
//     // Add more commands as needed
// ];

// const conn = new Client();
// conn
//     .on('keyboard-interactive', function (name, instructions, instructionsLang, prompts, finish) {
//         console.log('Connection :: keyboard-interactive');
//         finish([connSettings.password]);
//     })
//     .on('ready', function () {
//         console.log('Connected');
//         conn.exec(commands.join(' && '), function (err, stream) {
//             if (err) throw err;
//             stream.on('close', function (code, signal) {
//                 console.log('Deployment finished');
//                 conn.end();
//             }).on('data', function (data) {
//                 console.log('STDOUT: ' + data);
//             }).stderr.on('data', function (data) {
//                 console.log('STDERR: ' + data);
//             });
//         });
//     }).connect(connSettings);

// sftp.on('keyboard-interactive', function (name, instructions, instructionsLang, prompts, finish) {
//     console.log('Connection :: keyboard-interactive');
//     finish([connSettings.password]);
// })
//     .connect(connSettings)

//     .then(() => {
//         return sftp.list('/');
//     })
//     .then(data => {
//         console.log(data, 'the data info');
//     })
//     .catch(err => {
//         console.log(err, 'catch error');
//     });
// console.log(1234);



// conn.on('ready', function () {
//     console.log('Connected');
//     conn.exec(commands.join(' && '), function (err, stream) {
//         if (err) throw err;
//         stream.on('close', function (code, signal) {
//             console.log('Deployment finished');
//             conn.end();
//         }).on('data', function (data) {
//             console.log('STDOUT: ' + data);
//         }).stderr.on('data', function (data) {
//             console.log('STDERR: ' + data);
//         });
//     });
// }).connect(connSettings);
async function deployFolder() {
    const localFolder = '/path/to/local/folder';
    const remoteFolder = '/path/to/remote/folder';

    try {
        await sftp.connect(connSettings);
        // await sftp.uploadDir(localFolder, remoteFolder);
        console.log('Folder upload successful');
    } catch (err) {
        console.error('Error:', err.message);
    } finally {
        await sftp.end();
    }
}

deployFolder();
