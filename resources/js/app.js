/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
const axios = require("axios");

const form = document.getElementById('form');
const inputMessage = document.getElementById('input-message');
const listMessage = document.getElementById('list-messages');
const inputUsername = document.getElementById('input-username');
const inputPassword = document.getElementById('input-password');
const avatars = document.getElementById('avatars');
const spanTyping = document.getElementById('span-typing');



form.addEventListener('submit', function(event) {
    event.preventDefault();

    const userInput = inputMessage.value;

    axios.post('/chat-message', {
        message: userInput
    })

    inputMessage.value = "";

});

function getCookie(name){
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return parts.pop().split(';').shift();
    }
}

function request(url, options){
    // get cookie
    const csrfToken = getCookie('XSRF-TOKEN');
    return fetch(url, {
        headers: {
            'content-type': 'application/json',
            'accept': 'application/json',
            'X-XSRF-TOKEN': decodeURIComponent(csrfToken),
        },
        credentials: 'include',
        ...options,
    })
}

function logout(){
    return request('/logout', {
        method: 'GET'
    });
}

function login(username, password){

    return fetch('/sanctum/csrf-cookie', {
        headers: {
            'content-type': 'application/json',
            'accept': 'application/json'
        },
        credentials: 'include'
    }).then(() => logout())
    .then(() => {
        return request('/login', {
            method: "POST",
            body: JSON.stringify({
                user_name: username,
                'password': password
            })
        });
    }).then(() => {
        document.getElementById('section-login').classList.add('hide');
        document.getElementById('section-chat').classList.remove('hide');
    })
}

let usersOnline = [];

// function userInitial(username){
//     const names = username.split(' ');
//     return names.map((name) => name[0]).join("").toUpperCase();
// }

function renderAvatars(){
    avatars.textContent = "";
    usersOnline.forEach((user) => {
        const span = document.createElement('span');
        // span.textContent = userInitial(user.user_name);
        span.textContent = user.user_name;
        span.classList.add('avatar');
        avatars.append(span);
    })
}

function addChatMessage(name, message, color="black"){
    const li = document.createElement('li');
        
    li.classList.add('d-flex', 'flex-col');

    const span = document.createElement('span')
    span.classList.add('message-author');
    span.textContent = name;

    const messageSpan = document.createElement('span');
    messageSpan.textContent = message;

    messageSpan.style.color = color;

    li.append(span, messageSpan);

    listMessage.append(li);
}

document.getElementById('form-login').addEventListener('submit', function(event){
    event.preventDefault();
    const userName = inputUsername.value;
    const userPassword = inputPassword.value;

    login(userName, userPassword)
        .then(() => {

            const channel = Echo.join('presence.chat.1');

            inputMessage.addEventListener('input', function(event){
                if(inputMessage.value.length === 0){
                    channel.whisper('stop-typing');
                }else{
                    channel.whisper('typing',{
                        userName: userName,
                    })
                }
            })
        
            channel.here((users) => {
                usersOnline = [...users];
                renderAvatars();
                console.log({users});
            })
            .joining((user) => {
                console.log({user}, 'Joined');
                usersOnline.push(user);
                renderAvatars();
                addChatMessage(user.user_name, "has joined the room!");
            })
            .leaving((user) => {
                console.log({user}, 'Leaving');
                usersOnline = usersOnline.filter((userOnline) => userOnline.id !== user.id);
                renderAvatars();
                addChatMessage(user.user_name, "has left the room.", 'grey');

            })
            
            .listen('.chat-message', (event) => {
                const message = event.message;
            
                addChatMessage(event.user.user_name, message);
            })
            .listenForWhisper('typing', (event) => {
                spanTyping.textContent = event.userName + ' is typing...';
            })
            .listenForWhisper('stop-typing', (event) => {
                spanTyping.textContent = "";
            })
        })
})



window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
