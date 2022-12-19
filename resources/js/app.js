/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

const UserId = document.getElementById("userId").value;

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

        console.log(mr,wr);
        document.getElementById("spanMeronReward").textContent = number_format(Math.ceil(mr, 0));
        document.getElementById("spanWalaReward").textContent = number_format(Math.ceil(wr, 0));
    }

    document.getElementById("totalMeronBet").textContent= number_format(event.allMeronBet);
    document.getElementById("totalWalaBet").textContent= number_format(event.allWalaBet);
    document.getElementById("totalDrawBet").textContent= number_format(event.allDrawBet);
    document.getElementById("meronPayout").textContent= number_format(event.meronPayout, 1);
    document.getElementById("walaPayout").textContent= number_format(event.walaPayout, 1);

});

const channel1 = Echo.channel('status');

channel1.listen('.player-status', (event) => {

    document.getElementById("spanStatus").textContent = event.status;

    console.log();

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

    if(event.isCurrentFight){
        console.log(event);

        document.getElementById('spanResult').removeAttribute('class');
    
    if(event.result == 'meron'){
        document.getElementById('spanResult').classList.add('bg-danger');
        console.log( document.getElementById('spanResult').id);
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
        console.log(event);

        document.getElementById('spanLastResult').removeAttribute('class');
    
        if(event.result == 'meron'){
            document.getElementById('spanLastResult').classList.add('bg-danger');
            console.log( document.getElementById('spanLastResult').id);
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
            document.getElementById('spanLastResult').textContent = (event.fightNumber - 1) + ' ' + event.result.toString().toUpperCase();

            document.getElementById('spanResult').removeAttribute('class');

            document.getElementById('spanResult').classList.add('p-1');
            document.getElementById('spanResult').textContent = event.fightNumber;
    }


    

})

const channel4 = Echo.channel('balance');

channel4.listen('.player-balance', (event) => {

    if(UserId == event.userId){

        var totalBalance = 0;
        var currentBalance = document.getElementById("current_balance").innerHTML;
        currentBalance = currentBalance.slice(1);

        totalBalance = parseInt(removeComma(currentBalance)) + parseInt(event.reward);

        console.log(removeComma(currentBalance) , parseInt(event.reward));
        document.getElementById("current_balance").textContent = '$'+ number_format(totalBalance);
    }

    document.getElementById("totalMeronBet").textContent= 0;
    document.getElementById("totalWalaBet").textContent= 0;
    document.getElementById("totalDrawBet").textContent= 0;
    document.getElementById("meronPayout").textContent= 0;
    document.getElementById("walaPayout").textContent= 0;
    document.getElementById("meronBet").textContent= 0;
    document.getElementById("spanMeronReward").textContent= 0;
    document.getElementById("walaBet").textContent= 0;
    document.getElementById("spanWalaReward").textContent= 0;
    document.getElementById("draw-amount-bet").textContent= 0;
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
