/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

const UserId = document.getElementById("userId").value;
let avatars;


let usersOnline = [];
let operators = [];
let listMessage;

const presenceChannel = Echo.join('presence.chat.1');

presenceChannel.here((users) => {
    usersOnline = [...users];
    renderAvatars();
    // console.log({users});
})
.joining((user) => {
    // console.log({user}, 'Joined' ,usersOnline.length);
    usersOnline.push(user);
    renderAvatars();
})
.leaving((user) => {
    // console.log({user}, 'Leaving');
    usersOnline = usersOnline.filter((userOnline) => userOnline.id !== user.id);
    renderAvatars();
})

function renderAvatars(){
    usersOnline.forEach((user) => {
        if(user.role_type == 'Operator' || user.role_type == 'Declarator'){
            operators.push(user);
            operators = [...new Set(operators)]
        }
    })

    operators.forEach((user) => {
        if(user.id == UserId){
            avatars = document.getElementById('avatars');

            document.getElementById("onlineUsers").textContent = ' ' + usersOnline.length;
            addOnline();
        }
    })
}

function addBet(name, message, color="white"){
    listMessage = document.getElementById('list-messages');

    const li = document.createElement('li');
        
    li.classList.add('d-flex', 'flex-col');

    const span = document.createElement('span')
    span.classList.add('message-author');
    span.classList.add('text-warning');
    span.textContent = name + " /";

    const messageSpan = document.createElement('span');
    messageSpan.textContent = message;

    messageSpan.style.color = color;

    li.append(span, messageSpan);

    listMessage.append(li);
}

function addOnline(){
    
    avatars.textContent = "";
    usersOnline.forEach((user) => {
        const span = document.createElement('span');
        const hr = document.createElement('hr');
        // span.textContent = userInitial(user.user_name);
        span.textContent = user.user_name;
        span.classList.add('avatar');
        span.classList.add('font-bold');
        avatars.append(span, hr);
    })
}

const channel = Echo.channel('bettings');

channel.subscribe( () => {
    console.log('subscribbed!');
});


channel.listen('.player-bet', (event) => {

    if(UserId != event.userId){
        var mr = 0;
        var wr = 0;
        
        var meronBet = document.getElementById("meronBet").innerHTML;
        var walaBet = document.getElementById("walaBet").innerHTML;

        mr = removeComma(meronBet) * event.meronPayout / 100;
        wr = removeComma(walaBet) * event.walaPayout / 100;

        document.getElementById("spanMeronReward").textContent = number_format(Math.ceil(mr, 0));
        document.getElementById("spanWalaReward").textContent = number_format(Math.ceil(wr, 0));
       
    }
    
    operators.forEach((user) => {
        if(user.id == UserId){
            if(user.role_type == "Operator"){
                addBet(event.userName , ' Bet on ' + event.betOn + ' = ' + event.amount);
            }else{
                addBet(event.userName , ' Bet on ' + event.betOn + ' = ' + event.amount);
            }

            document.getElementById("totalRealMeronBet").textContent= "( " +number_format(event.allRealMeronBet)+ " )";
            document.getElementById("totalRealWalaBet").textContent= "( " +number_format(event.allRealWalaBet)+ " )";
            
        }
    })

    document.getElementById("totalMeronBet").textContent= number_format(event.allMeronBet);
    document.getElementById("totalWalaBet").textContent= number_format(event.allWalaBet);
    document.getElementById("totalDrawBet").textContent= number_format(event.allDrawBet);
    document.getElementById("meronPayout").textContent= number_format(event.meronPayout, 1);
    document.getElementById("walaPayout").textContent= number_format(event.walaPayout, 1);
    

});

const channel1 = Echo.channel('status');

channel1.listen('.player-status', (event) => {

    document.getElementById("spanStatus").textContent = event.status;

})

const channel2 = Echo.channel('fights');

channel2.listen('.player-fight', (event) => {
    if(event.isOpen == 0){
        document.getElementById('isOpen').classList.add('bg-danger');
        document.getElementById('isOpen').classList.remove('bg-success');
        document.getElementById("isOpen").textContent = "CLOSE";

        document.getElementById('meron').classList.add('hide');
        document.getElementById('meron1').classList.remove('hide');
        document.getElementById('wala').classList.add('hide');
        document.getElementById('wala1').classList.remove('hide');
        document.getElementById('draw').classList.add('hide');
        document.getElementById('draw1').classList.remove('hide');
    }else{
        document.getElementById('isOpen').classList.remove('bg-danger');
        document.getElementById('isOpen').classList.add('bg-success');
        document.getElementById("isOpen").textContent = "OPEN";

        document.getElementById('meron1').classList.add('hide');
        document.getElementById('meron').classList.remove('hide');
        document.getElementById('wala1').classList.add('hide');
        document.getElementById('wala').classList.remove('hide');
        document.getElementById('draw1').classList.add('hide');
        document.getElementById('draw').classList.remove('hide');
    }
    
})

const channel3 = Echo.channel('result');

channel3.listen('.player-result', (event) => {

    meronResult = 0;
    walaResult = 0;
    drawResult= 0;
    cancelResult = 0;
    result = [];
    event.response.reduce(function (r, a) {
        if (a.result !== r) {
            result.push([]);
        }
        result[result.length - 1].push(a);
        return a.result;
    }, undefined);

    var d = JSON.stringify(result, 0, 4);

    var jsonParse = JSON.parse(d);


    ctr = 0;
    number = 0;
    checkerNumber = 0;
    columnCount = Math.ceil(event.response.length / 7);


    var html = '';
    html += '<tr>';

    while(ctr < columnCount){
        checkerNumber += 7;
        html += '<td>';
            for(var x = 0; x < event.response.length; x++){

                if(number >= checkerNumber || number == event.response.length){
                    break;
                }
                if(event.response[number]['result'] == 'meron'){
                    meronResult++;
                    html += '<button type="button" class="btn trend_output btn_result" style="background: #ED5659;">'+event.response[number]['fight_number']+'</button><br>';
                }else if(event.response[number]['result']  == 'wala'){
                    walaResult++;
                    html += '<button type="button" class="btn trend_output btn_result" style="background: #1072BA;">'+event.response[number]['fight_number']+'</button><br>';
                }else if(event.response[number]['result']  == 'draw'){
                    drawResult++;
                    html += '<button type="button" class="btn trend_output btn_result" style="background: #198754;">'+event.response[number]['fight_number']+'</button><br>';
                }else if(event.response[number]['result'] == 'cancel'){
                    cancelResult++;
                    html += '<button type="button" class="btn trend_output btn_result" style="background: #999999;">'+event.response[number]['fight_number']+'</button><br>';
                }

                number++;
            }
        ctr++;
        html += '</td>';
    }


    $('#result-meron').html(meronResult);
    $('#result-wala').html(walaResult);
    $('#result-draw').html(drawResult);
    $('#result-cancel').html(cancelResult);

    html += '</tr>';

    $('#display_trade_group').html(html);

    var html2 = '';
        html2 += '<tr>';
        jsonParse.forEach(function(el){
            html2 += '<td>';
                el.forEach(function(ele){
                    if(ele.result == 'meron'){
                        meronResult++;
                        html2 += '<button type="button" class="btn trend_output btn_result" style="background: #ED5659;"></button><br>';
                    }else if(ele.result == 'wala'){
                        walaResult++;
                        html2 += '<button type="button" class="btn trend_output btn_result" style="background: #1072BA;"></button><br>';
                    }else if(ele.result == 'draw'){
                        drawResult++;
                        html2 += '<button type="button" class="btn trend_output btn_result" style="background: #198754;"></button><br>';
                    }
                })
                html2 += '</td>';
        })


        html2 += '</tr>';

        $('#display_trade_group2').html(html2);

    if(event.isCurrentFight){

        document.getElementById('spanResult').removeAttribute('class');
    
    if(event.result == 'meron'){
        document.getElementById('spanResult').classList.add('bg-danger');
    }
    if(event.result == 'wala'){
        document.getElementById('spanResult').classList.add('bg-primary');
        document.getElementById('spanResult').value = "wala";

    }
    if(event.result == 'draw'){
        document.getElementById('spanResult').classList.add('bg-success');
        document.getElementById('spanResult').value = "draw";

    }
    if(event.result == 'cancel'){
        document.getElementById('spanResult').classList.add('bg-light-secondary');
        document.getElementById('spanResult').value = "cancel";

    }
    
        document.getElementById('spanResult').classList.add('p-1');
        document.getElementById('spanResult').textContent = event.fightNumber + ' ' + event.result.toString().toUpperCase();
    }else{

        document.getElementById('spanLastResult').removeAttribute('class');
    
        if(event.result == 'meron'){
            document.getElementById('spanLastResult').classList.add('bg-danger');
        }
        if(event.result == 'wala'){
            document.getElementById('spanLastResult').classList.add('bg-primary');
            document.getElementById('spanLastResult').value = "wala";

        }
        if(event.result == 'draw'){
            document.getElementById('spanLastResult').classList.add('bg-success');
            document.getElementById('spanLastResult').value = "draw";

        }
        if(event.result == 'cancel'){
            document.getElementById('spanLastResult').classList.add('bg-light-secondary');
            document.getElementById('spanLastResult').value = "cancel";
        }
        
            document.getElementById('spanLastResult').classList.add('p-1');
            document.getElementById('spanLastResult').textContent = (event.lastFightNumber) + ' ' + event.result.toString().toUpperCase();

            document.getElementById('spanResult').removeAttribute('class');

            document.getElementById('spanResult').classList.add('p-1');
            document.getElementById('spanResult').textContent = event.fightNumber;

            document.getElementById("fightId").value = event.fightId;
    }


    

})

const channel4 = Echo.channel('balance');

channel4.listen('.player-balance', (event) => {

    if(UserId == event.userId){

        var totalBalance = 0;
        var currentBalance = document.getElementById("current_balance").innerHTML;
        currentBalance = currentBalance.slice(1);

        totalBalance = parseInt(removeComma(currentBalance)) + parseInt(event.reward);

        document.getElementById("current_balance").textContent = '$'+ number_format(totalBalance);
    }

    document.getElementById("totalMeronBet").textContent= 0;
    document.getElementById("totalWalaBet").textContent= 0;
    document.getElementById("totalDrawBet").textContent= 0;
    document.getElementById("meronPayout").textContent= 0.0;
    document.getElementById("walaPayout").textContent= 0.0;
    document.getElementById("meronBet").textContent= 0;
    document.getElementById("spanMeronReward").textContent= 0;
    document.getElementById("walaBet").textContent= 0;
    document.getElementById("spanWalaReward").textContent= 0;
    document.getElementById("draw-amount-bet").textContent= 0;
})

const channel5 = Echo.channel('refresh');

channel5.listen('.player-refresh', (event) => {

    if(UserId != event.userId){
        location.reload();
    }else{
        window.location.href = '/events';
    }
})


function removeComma(amount) {
    if (amount != null) {
        if (amount.toString().indexOf(',') > -1) { amount = amount.toString().replace(',', ''); }
        if (amount.toString().indexOf(',') > -1) { amount = amount.toString().replace(',', ''); }
    }
    return parseInt(amount);
}

function number_format(number, decimals, dec_point, thousands_point) {

    if (number == null || !isFinite(number)) {
        throw new TypeError("number is not valid");
    }

    if (!decimals) {
        var len = number.toString().split('.').length;
        decimals = len > 1 ? len : 0;
    }

    if (!dec_point) {
        dec_point = '.';
    }

    if (!thousands_point) {
        thousands_point = ',';
    }

    number = parseFloat(number).toFixed(decimals);

    number = number.replace(".", dec_point);

    var splitNum = number.split(dec_point);
    splitNum[0] = splitNum[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_point);
    number = splitNum.join(dec_point);

    return number;
}




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
