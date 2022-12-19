$(function () {
    var chatHub = $.connection.chatHub; $.connection.hub.qs = "arenaId=" + getQueryStringValueByKey("aid"); registerClientMethods(chatHub)
    $.connection.hub.start().done(function () { registerEvents(chatHub); displayPayout(); displayBaccaratResult(); });
}); function getQueryStringValueByKey(key) { var url = window.location.href; var values = url.split(/[\?&]+/); for (i = 0; i < values.length; i++) { var value = values[i].split("="); if (value[0] == key) { return value[1]; } } }
function htmlEncode(value) { var encodedValue = $('<div />').text(value).html(); return encodedValue; }
function IsNumeric(input) { var RE = /^-{0,1}\d*\.{0,1}\d+$/; return (RE.test(input)); }
function registerEvents(chatHub) {
    var userName = $('#usernameId').val(); var userId = $('#hdUserId').val(); var gameProgress = $('#hdGameProgress').val(); SetGameProgress(gameProgress); chatHub.server.connect(userName, userId); var status = $("#lblStatus").attr("data-name"); computePayout(); if (status == "open") { EnableBetButtons(true); }
    else { EnableBetButtons(false); AddWinningPersonalBets(); }
    $('#btnGameNum').click(function () { var gameNum = $("#txtGameNum").val(); if (confirm('Are you sure you want to CHANGE fight number to "' + gameNum + '"?')) { var arenaGameId = $('#hdCurrentNumber').val(); if (isNumber(gameNum)) { chatHub.server.changeFightNumber(arenaGameId, gameNum); } } }); $('#GetChats').change(function () { var chat = this.value; if (chat != '') { chatHub.server.setChat(chat); } }); $('#btnMeronBet-1').click(function () {
        var isTrue = 0; var betAmount = 0; var bet = $("input:radio[name=rbBetAmount]:checked").val()
        var betAmountZero = $("#txtBetAmountZero").val(); if ($(this).attr('data-name') == 'C' || $(this).attr('data-name') == 'R') { }
        else {
            if (betAmountZero != '') {
                if (betAmountZero > 0 || betAmountZero == 'ALL') {
                    var betOn = $(this).attr('data-name'); betAmount = betAmountZero; var betWord = ''; if (betOn == 'M') { betWord = "MERON"; }
                    if (betOn == 'W') { betWord = "WALA"; }
                    if (confirm('Are you sure you want to BET ' + betAmountZero + ' - ' + betWord + '?')) { $(this).toggleClass('clicked'); $(this).attr('disabled', 'disabled'); var oddId = $(this).attr('data-id'); var arenaGameId = $('#hdArenaGameID').val(); var userId = $('#hdUserId').val(); $("#txtBetAmountZero").val('0'); chatHub.server.saveBet(arenaGameId, betAmount, betOn, oddId, userId); $(this).removeAttr("disabled", "disabled"); }
                }
                else { alert('Invalid Bet Amount!'); $("#txtBetAmountZero").val('0'); }
            }
            else { alert('Enter Bet Amount!'); }
        }
    }); $('#btnWalaBet-1').click(function () {
        var isTrue = 0; var betAmount = 0; var bet = $("input:radio[name=rbBetAmount]:checked").val()
        var betAmountZero = $("#txtBetAmountZero").val(); if ($(this).attr('data-name') == 'C' || $(this).attr('data-name') == 'R') { }
        else {
            if (betAmountZero != '') {
                if (betAmountZero > 0 || betAmountZero == 'ALL') {
                    var betOn = $(this).attr('data-name'); betAmount = betAmountZero; var betWord = ''; if (betOn == 'M') { betWord = "MERON"; }
                    if (betOn == 'W') { betWord = "WALA"; }
                    if (confirm('Are you sure you want to BET ' + betAmountZero + ' - ' + betWord + '?')) { $(this).toggleClass('clicked'); $(this).attr('disabled', 'disabled'); var oddId = $(this).attr('data-id'); var arenaGameId = $('#hdArenaGameID').val(); var userId = $('#hdUserId').val(); $("#txtBetAmountZero").val('0'); chatHub.server.saveBet(arenaGameId, betAmount, betOn, oddId, userId); $(this).removeAttr("disabled", "disabled"); }
                }
                else { alert('Invalid Bet Amount!'); $("#txtBetAmountZero").val('0'); }
            }
            else { alert('Enter Bet Amount!'); }
        }
    }); $('#btnDrawBet').click(function () {
        var isTrue = 0; var betAmount = 0; var bet = $("input:radio[name=rbBetAmount]:checked").val()
        var betAmountZero = $("#txtBetAmountZero").val(); if (betAmountZero != '') {
            if (betAmountZero > 0) { betAmount = betAmountZero; if (confirm('Are you sure you want to BET ' + betAmountZero + ' - DRAW?')) { var betOn = $(this).attr('data-name'); var oddId = $(this).attr('data-id'); var arenaGameId = $('#hdArenaGameID').val(); var userId = $('#hdUserId').val(); $("#txtBetAmountZero").val('0'); chatHub.server.saveBet(arenaGameId, betAmount, betOn, oddId, userId); } }
            else { alert('Invalid Bet Amount!'); $("#txtBetAmountZero").val('0'); }
        }
        else { alert('Enter Bet Amount!'); }
    }); $('#btnGameOpen').click(function () { if (confirm('Are you sure you want to OPEN this fight?')) { var fightNumber = $('#lblGameInfoGameNumber').html(); var username = $('#usernameId').val(); var arenaGameId = $('#hdCurrentNumber').val(); chatHub.server.openBet(arenaGameId, username); displayAdminMessage('OPEN button clicked'); setAdminButtons('OPEN'); SetGameProgress('OPEN'); } }); $('#btnGameClose').click(function () { if (confirm('Are you sure you want to CLOSE this fight?')) { var fightNumber = $('#lblGameInfoGameNumber').html(); var tdPayOutMeron = $('#tdPayOutMeron').html(); var tdPayOutWala = $('#tdPayOutWala').html(); var username = $('#usernameId').val(); var arenaGameId = $('#hdCurrentNumber').val(); chatHub.server.closeBet(arenaGameId, username, tdPayOutMeron, tdPayOutWala); displayAdminMessage('CLOSE button clicked (do not refresh)'); setAdminButtons('CLOSE'); SetGameProgress('CLOSE'); } }); $('#btnDeclareMeron').click(function () {
        if (confirm('Are you sure that the winner of this fight is MERON?')) {
            var llamadoDehado = $("input:radio[name=rbLlamadoDehado]:checked").val(); var fightNumber = $('#lblGameInfoGameNumber').html(); var winner = $(this).attr('data-name'); var arenaGameId = $('#hdCurrentNumber').val(); var userId = $('#hdUserId').val(); chatHub.server.declareWinner(arenaGameId, winner, userId, llamadoDehado); EnableBetButtons(false); $("#hdGameInfoResult").val("M")
            $("#hdGameInfoResultLlmadoDehado").val(llamadoDehado); displayAdminMessage('MERON button clicked (do not refresh)'); setAdminButtons('DECLARE'); SetGameProgress('DECLARE');
        }
    }); $('#btnDeclareWala').click(function () {
        if (confirm('Are you sure that the winner of this fight is WALA?')) {
            var llamadoDehado = $("input:radio[name=rbLlamadoDehado]:checked").val(); var fightNumber = $('#lblGameInfoGameNumber').html(); var winner = $(this).attr('data-name'); var arenaGameId = $('#hdCurrentNumber').val(); var userId = $('#hdUserId').val(); chatHub.server.declareWinner(arenaGameId, winner, userId, llamadoDehado); EnableBetButtons(false); $("#hdGameInfoResult").val("W")
            $("#hdGameInfoResultLlmadoDehado").val(llamadoDehado); displayAdminMessage('WALA button clicked (do not refresh)'); setAdminButtons('DECLARE'); SetGameProgress('DECLARE');
        }
    }); $('#btnDeclareDraw').click(function () {
        if (confirm('Are you sure that the fight is DRAW?')) {
            var winner = $(this).attr('data-name'); var arenaGameId = $('#hdCurrentNumber').val(); var fightNumber = $('#lblGameInfoGameNumber').html(); var userId = $('#hdUserId').val(); chatHub.server.declareWinner(arenaGameId, winner, userId, ""); EnableBetButtons(false); $("#hdGameInfoResult").val("D")
            $("#hdGameInfoResultLlmadoDehado").val(""); displayAdminMessage('DRAW button clicked (do not refresh)'); setAdminButtons('DECLARE'); SetGameProgress('DECLARE');
        }
    }); $('#btnDeclareCancel').click(function () {
        if (confirm('Are you sure that the fight is CANCEL?')) {
            var winner = $(this).attr('data-name'); var arenaGameId = $('#hdCurrentNumber').val(); var fightNumber = $('#lblGameInfoGameNumber').html(); var userId = $('#hdUserId').val(); chatHub.server.declareWinner(arenaGameId, winner, userId, ""); EnableBetButtons(false); $("#hdGameInfoResult").val("C")
            $("#hdGameInfoResultLlmadoDehado").val(""); displayAdminMessage('CANCEL button clicked (do not refresh)'); setAdminButtons('DECLARE'); SetGameProgress('DECLARE');
        }
    }); $('#btnResetAll').click(function () { var arenaGameId = $('#hdCurrentNumber').val(); var arenaId = $('#hdArenaId').val(); chatHub.server.resetCurrentUserBets(arenaId, arenaGameId); displayAdminMessage('RESET ALL button clicked'); setAdminButtons('RESET'); SetGameProgress('RESET'); }); $('#btnGoNext').click(function () { if (confirm('Are you sure you want to GO TO NEXT FIGHT?')) { var arenaGameId = $('#hdCurrentNumber').val(); var arenaId = $('#hdArenaId').val(); chatHub.server.goToNextGame(arenaId, arenaGameId); displayAdminMessage(''); setAdminButtons('NEW'); SetGameProgress('NEW'); } }); $('#btnRefreshEntry').click(function () { if (confirm('Are you sure you want to REFRESH ENTRY NAMES?')) { var arenaGameId = $('#hdArenaGameID').val(); } }); $('#btnOurPromo').click(function () { if (confirm('Are you sure you want to Display PROMO?')) { chatHub.server.displayPromo(); } }); $('#btnEmergencyClose').click(function () { if (confirm('Are you sure you want to use EMERGENCY CLOSE?')) { var arenaGameId = $('#hdArenaGameID').val(); chatHub.server.updateGameStatus(arenaGameId, false); } }); $('#btnRefreshAll').click(function () { chatHub.server.refreshAll(); }); $('#btnMatchBet').click(function () { if (confirm('Are you sure you want to MATCH BET?')) { var arenaGameId = $('#hdArenaGameID').val(); chatHub.server.matchDeleteGame(arenaGameId); } });
}
function clearAdminProgress() { $("#lblAdminDeclareResult").html(""); $("#lblAdminBettingStatus").html(""); $("#lblAdminNexFight").html(""); }
function registerClientMethods(chatHub) {
    chatHub.client.SendMessageToAll = function (userName, message) { AddMessage(userName, message); }; chatHub.client.onConnected = function (id, userName, userId, allUsers, messages) { $('#hdId').val(id); DisplayUserCount(allUsers.length); displayWhoIsOnline(allUsers, ''); for (i = 0; i < messages.length; i++) { AddMessageTimeStamp(messages[i].UserName, messages[i].Message, messages[i].TimeStamp); } }; chatHub.client.redirectToHome = function () { alert('Multiple session detected!'); window.location = "../"; }; chatHub.client.refreshAllUsers = function (arenaId) { alert('Please standby site will refresh!'); window.location = "../Console/Arena?aid=" + arenaId; }; chatHub.client.onNewUserConnected = function (id, name, allUsers) { DisplayUserCount(allUsers); displayWhoIsOnline(allUsers, ''); }; chatHub.client.messageReceived = function (userName, message, timeStamp) { AddMessageTimeStamp(userName, message, timeStamp); }; chatHub.client.getChat = function (chat) { $("#lblAdminMessageAll").html(chat); }; chatHub.client.systemMessageReceived = function (userName, message, timeStamp, isError) { if (isError == false) { AddMessageTimeStamp(userName, message, timeStamp); $("#lblStatus").attr('class', 'text-closed-bet'); $("#lblStatus").html("CLOSED"); EnableBetButtons(false); } }; chatHub.client.systemMessageReceivedClosed = function (isError) { if (isError == false) { $("#lblStatus").attr('class', 'text-closed-bet'); $("#lblStatus").html("CLOSED"); EnableBetButtons(false); } }; chatHub.client.onUserDisconnected = function (id, userName, allUsers) { DisplayUserCount(allUsers); displayWhoIsOnline(allUsers, userName); }; chatHub.client.gameUpdated = function (agid, isOpen, isReopen) {
        var fightNumber = $("#lblGameInfoGameNumber").html(); if (isReopen == "True") { alert('Please REFRESH the page before opening the fight'); if (isOpen == "False") { $("#lblStatus").attr('class', 'text-closed-bet'); $("#lblStatus").html("CLOSED"); } }
        if (isReopen == "False") {
            if (isOpen == "True") { EnableBetButtons(true); $("#lblStatus").html("OPEN"); }
            else { $("#lblStatus").attr('class', 'text-closed-bet'); $("#lblStatus").html("CLOSED"); EnableBetButtons(false); }
        }
    }; chatHub.client.openBetUpdate = function () { EnableBetButtons(true); $("#lblStatus").attr('class', 'text-open-bet'); $("#lblStatus").html("OPEN"); }; chatHub.client.closeBetUpdate = function () { $("#lblStatus").attr('class', 'text-closed-bet'); $("#lblStatus").html("CLOSED"); EnableBetButtons(false); }; chatHub.client.gameOpenMessage = function (message) { alert(message); }; chatHub.client.updateTotalBet = function (message, totalBets, oddId) { if (message == "") { $("#tdTotalBetMeron-" + oddId).html(ReplaceNumberWithCommas(totalBets.Meron)); $("#tdTotalBetWala-" + oddId).html(ReplaceNumberWithCommas(totalBets.Wala)); $("#tdTotalBetDraw-" + oddId).html(ReplaceNumberWithCommas(totalBets.Draw)); } }; chatHub.client.updateTotalBetUsers = function (message, oddId, Meron, Wala, Draw) { if (message == "") { $("#tdTotalBetMeron-" + oddId).html(ReplaceNumberWithCommas(Meron)); $("#tdTotalBetWala-" + oddId).html(ReplaceNumberWithCommas(Wala)); $("#tdTotalBetDraw-" + oddId).html(ReplaceNumberWithCommas(Draw)); } }; chatHub.client.updateTotalBetUser = function (message, oddId, totalTosend, betOn) {
        if (message == "") {
            if (betOn == "M") { if (totalTosend != "0" || totalTosend > 0) { $("#tdTotalBetMeron-" + oddId).html(ReplaceNumberWithCommas(totalTosend)); } }
            if (betOn == "W") { if (totalTosend != "0" || totalTosend > 0) { $("#tdTotalBetWala-" + oddId).html(ReplaceNumberWithCommas(totalTosend)); } }
            if (betOn == "D") { if (totalTosend != "0" || totalTosend > 0) { $("#tdTotalBetDraw-" + oddId).html(ReplaceNumberWithCommas(totalTosend)); } }
            computePayout();
        }
    }; chatHub.client.updateAllTotalBets = function (totalBets) {
        var closeButton = $("#lblStatus"); for (i = 0; i < totalBets.length; i++) {
            if (totalBets[i].Meron == "0") { $("#tdTotalBetWala-" + totalBets[i].OddId).html(ReplaceNumberWithCommas(0)); }
            else { $("#tdTotalBetWala-" + totalBets[i].OddId).html(ReplaceNumberWithCommas(totalBets[i].Wala)); }
            if (totalBets[i].Wala == "0") { $("#tdTotalBetMeron-" + totalBets[i].OddId).html(ReplaceNumberWithCommas(0)); }
            else { $("#tdTotalBetMeron-" + totalBets[i].OddId).html(ReplaceNumberWithCommas(totalBets[i].Meron)); }
        }
        if (closeButton.html() == "OPEN") { $("#lblStatus").attr('class', 'text-closed-bet'); $("#lblStatus").html("CLOSED"); }
        computePayout();
    }; chatHub.client.updateAllPersonalBets = function (personalBets) {
        var hdBetType = $('#hdBetType').val(); if (hdBetType != '0') {
            for (i = 0; i < 1; i++) {
                if ($("#tdPersonalBetMeron-1").html() != '' && $("#tdPersonalBetMeron-1" != '0')) { actualPayout(personalBets[i].Meron, 'm'); }
                $("#tdPersonalBetMeronMatched-" + personalBets[i].OddId).html(ReplaceNumberWithCommas(personalBets[i].MeronMatched))
                if ($("#tdPersonalBetWala-1").html() != '' && ($("#tdPersonalBetWala-1").html() != '0')) { actualPayout(personalBets[i].Wala, 'w'); }
                $("#tdPersonalBetWalaMatched-" + personalBets[i].OddId).html(ReplaceNumberWithCommas(personalBets[i].WalaMatched))
            }
        }
        else {
            for (i = 0; i < personalBets.length; i++) {
                $("#tdPersonalBetMeron-" + personalBets[i].OddId).html(ReplaceNumberWithCommas(WinningCalculation(personalBets[i].Meron, personalBets[i].OddName, 'm')))
                $("#tdPersonalBetMeronMatched-" + personalBets[i].OddId).html(ReplaceNumberWithCommas(personalBets[i].MeronMatched))
                $("#tdPersonalBetWala-" + personalBets[i].OddId).html(ReplaceNumberWithCommas(WinningCalculation(personalBets[i].Wala, personalBets[i].OddName, 'w')))
                $("#tdPersonalBetWalaMatched-" + personalBets[i].OddId).html(ReplaceNumberWithCommas(personalBets[i].WalaMatched))
            }
        }
        var userId = $('#hdUserId').val(); $.ajax({ cache: false, url: '/Arena/GetBalanceByIdSingle', type: "GET", data: { uid: userId }, dataType: "json", success: function (balance) { $("#lblBalancePoints").html(ReplaceNumberWithCommas(balance)); $("#lblTotalPointsDisplay").html(ReplaceNumberWithCommas(balance)); } });
    }; chatHub.client.updatePersonalBet = function (message, personalBets, oddId, balance) {
        if (message == "") {
            $("#tdPersonalBetMeron-" + oddId).html(ReplaceNumberWithCommas(personalBets.Meron))
            $("#tdPersonalBetMeronMatched-" + oddId).html(ReplaceNumberWithCommas(personalBets.MeronMatched))
            $("#tdPersonalBetWala-" + oddId).html(ReplaceNumberWithCommas(personalBets.Wala))
            $("#tdPersonalBetWalaMatched-" + oddId).html(ReplaceNumberWithCommas(personalBets.WalaMatched))
            $("#tdPersonalBetDraw-" + oddId).html(ReplaceNumberWithCommas(personalBets.Draw))
            $("#lblBalancePoints").html(ReplaceNumberWithCommas(balance)); $("#lblTotalPointsDisplay").html(ReplaceNumberWithCommas(balance));
        }
        else {
            $("#tdPersonalBetMeron-" + oddId).html(ReplaceNumberWithCommas(personalBets.Meron))
            $("#tdPersonalBetMeronMatched-" + oddId).html(ReplaceNumberWithCommas(personalBets.MeronMatched))
            $("#tdPersonalBetWala-" + oddId).html(ReplaceNumberWithCommas(personalBets.Wala))
            $("#tdPersonalBetWalaMatched-" + oddId).html(ReplaceNumberWithCommas(personalBets.WalaMatched))
            $("#tdPersonalBetDraw-" + oddId).html(ReplaceNumberWithCommas(personalBets.Draw))
            $("#lblBalancePoints").html(ReplaceNumberWithCommas(balance)); $("#lblTotalPointsDisplay").html(ReplaceNumberWithCommas(balance)); alert(message);
        }
    }; chatHub.client.updatePersonalBetNotification = function (message) { alert(message); }; chatHub.client.updatePersonalBetUser = function (message, oddId, balance, betToSend, matchedToSend, oddName, betOn, betID) {
        if (message == "") {
            if (betOn == "M") { $("#tdPersonalBetMeron-" + oddId).html(ReplaceNumberWithCommas(betToSend)); $("#tdPersonalBetMeronMatched-" + oddId).html(ReplaceNumberWithCommas(matchedToSend)); $("#lblMeronID").html(betID); }
            if (betOn == "W") { $("#tdPersonalBetWala-" + oddId).html(ReplaceNumberWithCommas(betToSend)); $("#tdPersonalBetWalaMatched-" + oddId).html(ReplaceNumberWithCommas(matchedToSend)); $("#lblWalaID").html(betID); }
            if (betOn == "D") { $("#tdPersonalBetDraw-" + oddId).html(ReplaceNumberWithCommas(betToSend)); }
            $("#lblBalancePoints").html(ReplaceNumberWithCommas(balance)); $("#lblTotalPointsDisplay").html(ReplaceNumberWithCommas(balance));
        }
        else {
            if (betOn == "M") { $("#tdPersonalBetMeron-" + oddId).html(ReplaceNumberWithCommas(betToSend)); $("#tdPersonalBetMeronMatched-" + oddId).html(ReplaceNumberWithCommas(matchedToSend)); }
            if (betOn == "W") { $("#tdPersonalBetWala-" + oddId).html(ReplaceNumberWithCommas(betToSend)); $("#tdPersonalBetWalaMatched-" + oddId).html(ReplaceNumberWithCommas(matchedToSend)); }
            if (betOn == "D") { $("#tdPersonalBetDraw-" + oddId).html(ReplaceNumberWithCommas(betToSend)); }
            $("#lblBalancePoints").html(ReplaceNumberWithCommas(balance)); $("#lblTotalPointsDisplay").html(ReplaceNumberWithCommas(balance)); alert(message);
        }
    }; chatHub.client.updatePersonalBetByOddId = function (meronMatched, walaMatched, oddId) {
        var hdBetType = $('#hdBetType').val(); if (hdBetType != '0') { $("#tdPersonalBetMeronMatched-" + oddId).html(ReplaceNumberWithCommas(meronMatched)); $("#tdPersonalBetWalaMatched-" + oddId).html(ReplaceNumberWithCommas(walaMatched)); }
        else { $("#tdPersonalBetMeronMatched-" + oddId).html(ReplaceNumberWithCommas(meronMatched)); $("#tdPersonalBetWalaMatched-" + oddId).html(ReplaceNumberWithCommas(walaMatched)); }
    }; chatHub.client.sendBetMessage = function (message) { if (message != "") { alert(message); } }; chatHub.client.updateWinnings = function (winner, llamadoDehado) {
        var message = ""; var className = "info"; var fightNumber = $("#lblGameInfoGameNumber").html(); var result = ""; var winning = ""; $("#hdGameInfoResult").val(winner); $("#hdGameInfoResultLlmadoDehado").val(llamadoDehado); EnableBetButtons(false); if (winner == "M") { winning = "MERON"; message = "Fight #" + fightNumber + " : Winner is WALA"; className = "information"; result = "meronresult"; }
        else if (winner == "W") { winning = "WALA"; message = "Fight #" + fightNumber + " : Winner is WALA"; className = "error"; result = "walaresult"; }
        else if (winner == "C") { winning = "CANCEL"; message = "Fight #" + fightNumber + " : GAME IS CANCELLED"; className = "notification"; result = "cancelresult"; }
        else if (winner == "D") { winning = "DRAW"; message = "Fight #" + fightNumber + " : RESULT IS DRAW"; className = "warning"; result = "drawresult"; }
        $("#lblWinnerFight").removeAttr("class"); $("#lblWinnerFight").attr('class', result); $("#lblWinnerFight").html(fightNumber + ": " + winning); ShowNotification(message, "bottom", className);
    }; chatHub.client.refreshPoints = function (totalPoints) { ResetTotalBets(); ResetPersonalBets(); ResetOwnerName(); SetPoints(totalPoints); var fightNumber = $("#lblGameInfoGameNumber").html(); var winner = $("#hdGameInfoResult").val(); var llamadoDehado = $("#hdGameInfoResultLlmadoDehado").val(); }; chatHub.client.resetCurrentPoints = function () { ResetTotalBets(); ResetPersonalBets(); var userId = $('#hdUserId').val(); $.ajax({ cache: false, url: '/Arena/GetBalanceById', type: "GET", data: { uid: userId }, dataType: "json", success: function (balance) { SetPoints(balance); } }); var fightNumber = $("#lblGameInfoGameNumber").html(); var winner = $("#hdGameInfoResult").val(); var llamadoDehado = $("#hdGameInfoResultLlmadoDehado").val(); SetResult(fightNumber, winner, llamadoDehado); }; chatHub.client.getLatestGame = function (arenaGameId, gameNumber, isOpen, meron, wala, message, gameProgress, meronOwner, meronOwnerScore, walaOwner, walaOwnerScore, pendingPoints) {
        PendingPoints(pendingPoints); ResetTotalBets(); ResetPersonalBets(); EnableBetButtons(false); SetGameInfo(arenaGameId, gameNumber, isOpen, meron, wala, gameProgress, meronOwner, meronOwnerScore, walaOwner, walaOwnerScore); if (message != "") { ShowNotification(message, "bottomLeft", "error"); }
        else { ShowNotification("READY FOR Fight #" + gameNumber, "bottomLeft", "notification"); }
    }; chatHub.client.getLatestGame = function (arenaGameId, gameNumber, isOpen, meron, wala, message, gameProgress, meronOwner, meronOwnerScore, walaOwner, walaOwnerScore, pendingPoints, currentNumber) {
        PendingPoints(pendingPoints); ResetTotalBets(); ResetPersonalBets(); EnableBetButtons(false); var fightNumber = $("#lblGameInfoGameNumber").html(); var winner = $("#hdGameInfoResult").val(); SetGameInfoGame(arenaGameId, gameNumber, isOpen, meron, wala, gameProgress, meronOwner, meronOwnerScore, walaOwner, walaOwnerScore, currentNumber); if (message != "") { ShowNotification(message, "bottomLeft", "error"); }
        else { ShowNotification("READY FOR Fight #" + gameNumber, "bottomLeft", "notification"); }
        singleDisplayBaccaratResult(winner, fightNumber);
    }; chatHub.client.getArenaGameInfo = function (arenaGameId, gameNumber, meron, wala, meronScore, walaScore) { SetArenaGameInfo(arenaGameId, gameNumber, meron, wala, meronScore, walaScore); }; chatHub.client.notifyNoMoreFights = function (message) { ShowNotification(message, "bottomLeft", "information"); }; chatHub.client.displayOurPromo = function () {
        var stat = document.getElementById("overlayScreen").style.display; if (stat == 'none') { document.getElementById("overlayScreen").style.display = "block"; }
        else { document.getElementById("overlayScreen").style.display = "none"; }
    }; chatHub.client.changeFightNumber = function (fightNumber) { $("#lblGameInfoGameNumber").html(fightNumber); }; chatHub.client.systemMessageReceivedResetReOpen = function (userName, message, timeStamp) { ResetTotalBets(); ResetPersonalBets(); ResetOwnerName(); var userId = $('#hdUserId').val(); $.ajax({ cache: false, url: '/Arena/GetBalanceById', type: "GET", data: { uid: userId }, dataType: "json", success: function (balance) { SetPoints(balance); } }); AddMessageTimeStamp(userName, message, timeStamp); };
}
function WinningCalculation(amount, oddName, meronwala) {
    var plasada = parseFloat($('#hdPlasadaPercentage').val());
    var totalWinnings = 0; if (amount != null) { if (amount.toString().indexOf(',') > -1) { amount = amount.toString().replace(',', ''); } }
    if (oddName != 'undefined') {
        if (amount > 0) {
            var n = oddName.toString().split('-'); var n1 = n[0]; var n2 = n[1]; if (meronwala == 'm') { totalWinnings = (amount * (n2 / n1)) - ((amount * (n2 / n1)) * plasada); }
            if (meronwala == 'w') { totalWinnings = (amount * (n1 / n2)) - ((amount * (n1 / n2)) * plasada); }
            return '+' + parseFloat(totalWinnings).toFixed(0);
        }
    }
    return '';
}
function PendingPoints(pendingPoints) {
    if (pendingPoints.length > 0) { $("#pendingPoints").text('re-declare fight: ' + pendingPoints); }
    else { $("#pendingPoints").text(''); }
}
function DisplayUserCount(count) { $('#numberOnline').html(" - " + (count)); }
function AddMessage(userName, message) {
    var classname = 'text-warning'; var messageClass = ""; if (checkIfNameHas(userName, 'admin-')) { classname = 'admin'; messageClass = 'admin'; }
    if (checkIfNameHas(userName, 'csr-')) { classname = 'csr'; messageClass = 'csr'; }
    if (checkIfNameHas(userName, 'admin6')) { classname = 'excpeption'; messageClass = 'excpeption'; }
    if (message.indexOf("::happy") != -1) { message = message.replace(/::happy/g, " <img src='/img/emo-happy.gif' alt='happy' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::frown") != -1) { message = message.replace(/::frown/g, " <img src='/img/emo-frown.gif' alt='frown' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::heart") != -1) { message = message.replace(/::heart/g, " <img src='/img/emo-heart.gif' alt='heart' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::thumbsup") != -1) { message = message.replace(/::thumbsup/g, " <img src='/img/emo-thumbsup.gif' alt='thumbsup' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::thumbsdown") != -1) { message = message.replace(/::thumbsdown/g, " <img src='/img/emo-thumbsdown.gif' alt='thumbsdown' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::coffee") != -1) { message = message.replace(/::coffee/g, " <img src='/img/emo-coffee.gif' alt='coffee' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::cooking") != -1) { message = message.replace(/::cooking/g, " <img src='/img/emo-cooking.gif' alt='cooking' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::seaman") != -1) { message = message.replace(/::seaman/g, " <img src='/img/emo-seaman.gif' alt='seaman' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::popcorn") != -1) { message = message.replace(/::popcorn/g, " <img src='/img/emo-popcorn.gif' alt='popcorn' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::eating") != -1) { message = message.replace(/::eating/g, " <img src='/img/emo-eating.gif' alt='eating' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::cooking") != -1) { message = message.replace(/::cooking/g, " <img src='/img/emo-cooking.gif' alt='cooking' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::workout") != -1) { message = message.replace(/::workout/g, " <img src='/img/emo-workout.gif' alt='workout' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::hide") != -1) { message = message.replace(/::hide/g, " <img src='/img/emo-hide.gif' alt='hide' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::rope") != -1) { message = message.replace(/::rope/g, " <img src='/img/emo-rope.gif' alt='rope' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::shh") != -1) { message = message.replace(/::shh/g, " <img src='/img/emo-shh.gif' alt='shh' class='img-responsive small img-valign'/> "); }
    message = message.replace(/</g, "&lt;")
    message = message.replace(/http/g, "&lt;")
    message = message.replace(/&lt;img/g, "<img")
    message = message.replace(/http/g, " ")
    $('#discussion').append('<p class="text-custom-one chat-message  ' + messageClass + '"><strong class="' + classname + '">' + userName + ': </strong>'
        + message + '');
}
function EnableBetButtons(flag) {
    if (flag == true) { $("#btnDrawBet").removeAttr('disabled'); $("#btnMeronBet-1").removeAttr('disabled'); $("#btnWalaBet-1").removeAttr('disabled'); }
    else { $("#btnDrawBet").attr('disabled', 'disabled'); $("#btnMeronBet-1").attr('disabled', 'disabled'); $("#btnWalaBet-1").attr('disabled', 'disabled'); }
}
function ReplaceNumberWithCommas(yourNumber) {
    if (yourNumber !== null) {
        if (yourNumber.toString().indexOf('.') === -1) { return yourNumber.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); }
        else { var n = yourNumber.toString().split("."); n[0] = n[0].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); return n[0]; }
    }
    return "";
}
function ShowNotification(message, position, className) { }
function SetPoints(totalPoints) { $("#lblBalancePoints").html(ReplaceNumberWithCommas(totalPoints)); $("#lblTotalPointsDisplay").html(ReplaceNumberWithCommas(totalPoints)); }
function setAllPointsToBalance() { $("#lblTotalPointsDisplay").html($("#lblBalancePoints").html()); }
function SetResult(fightNumber, winner, llamadoDehado) {
    var totalWins = 0; var lblResultMeron = $("#lblResultMeron"); var lblResultWala = $("#lblResultWala"); var lblResultDraw = $("#lblResultDraw"); var lblResultCancel = $("#lblResultCancel"); var res = ""; var subClassName = ""; var llamadoClassName = ''; if (winner == "M") { subClassName = "meron"; totalWins = parseInt(lblResultMeron.html()) + 1; lblResultMeron.html(totalWins); res = "M"; }
    if (winner == "W") { subClassName = "wala"; totalWins = parseInt(lblResultWala.html()) + 1; lblResultWala.html(totalWins); res = "W"; }
    if (winner == "D") { subClassName = "draw"; totalWins = parseInt(lblResultDraw.html()) + 1; lblResultDraw.html(totalWins); res = "D"; }
    if (winner == "C") { subClassName = "cancel"; totalWins = parseInt(lblResultCancel.html()) + 1; lblResultCancel.html(totalWins); res = "C"; }
    $("#ulResult ul").prepend("<li class='" + subClassName + " '> <div class='circle " + subClassName + "'> " + fightNumber + "</div>" + winner + "</li>");
}
function SetGameInfo(arenaGameId, gameNumber, isOpen, meron, wala, gameProgress, meronOwner, meronOwnerScore, walaOwner, walaOwnerScore) {
    $("#lblGameInfoGameNumber").html(gameNumber); $("#txtGameNum").val(gameNumber); $("#hdArenaGameID").val(arenaGameId); if (isOpen == "true") { $("#lblStatus").html("OPEN"); }
    else { $("#lblStatus").attr('class', 'text-closed-bet'); $("#lblStatus").html("CLOSED"); }
    SetGameProgress(gameProgress);
}
function SetGameInfoGame(arenaGameId, gameNumber, isOpen, meron, wala, gameProgress, meronOwner, meronOwnerScore, walaOwner, walaOwnerScore, currentNumber) {
    $("#lblGameInfoGameNumber").html(gameNumber); $("#txtGameNum").val(gameNumber); $("#hdArenaGameID").val(arenaGameId); $("#hdCurrentNumber").val(currentNumber); if (isOpen == "true") { $("#lblStatus").html("OPEN"); }
    else { $("#lblStatus").html("CLOSED"); }
    SetGameProgress(gameProgress);
}
function SetGameProgress(gameProgress) {
    setAllPointsToBalance(); clearAdminProgress(); setAdminButtons(gameProgress); if (gameProgress == "NEW") { $("#lblAdminBettingStatus").html("[ACTIVE] - FOR OPEN"); }
    if (gameProgress == "OPEN") { $("#lblAdminBettingStatus").html("[ACTIVE] - FOR CLOSE"); }
    if (gameProgress == "CLOSE") { $("#lblAdminDeclareResult").html("[ACTIVE] - FOR RESULT"); }
    if (gameProgress == "DECLARE") { $("#lblAdminNexFight").html("[ACTIVE] - FOR RESET"); }
    if (gameProgress == "RESET") { $("#lblAdminNexFight").html("[ACTIVE] - FOR GO TO NEXT"); }
}
function setAdminButtons(gameProgress) {
    disableAdminButtons(); if (gameProgress == "NEW") { disableEnableButtons("#btnGameOpen", false); disableEnableButtons("#btnDeclareCancel", false); disableEnableButtons("#btnReOpen", true); }
    if (gameProgress == "OPEN") { disableEnableButtons("#btnGameClose", false); disableEnableButtons("#btnDeclareCancel", false); disableEnableButtons("#btnReOpen", false); }
    if (gameProgress == "CLOSE") { disableEnableButtons("#btnDeclareMeron", false); disableEnableButtons("#btnDeclareWala", false); disableEnableButtons("#btnDeclareDraw", false); disableEnableButtons("#btnDeclareCancel", false); disableEnableButtons("#btnReOpen", true); }
    if (gameProgress == "DECLARE") { setTimeout(function () { disableEnableButtons("#btnGoNext", true); disableEnableButtons("#btnResetAll", false); disableEnableButtons("#btnReOpen", true); }, 4000); }
    if (gameProgress == "RESET") { setTimeout(function () { disableEnableButtons("#btnGoNext", false); disableEnableButtons("#btnReOpen", true); }, 5000); }
}
function disableAdminButtons() { disableEnableButtons("#btnGameOpen", true); disableEnableButtons("#btnGameClose", true); disableEnableButtons("#btnDeclareMeron", true); disableEnableButtons("#btnDeclareWala", true); disableEnableButtons("#btnDeclareDraw", true); disableEnableButtons("#btnDeclareCancel", true); disableEnableButtons("#btnGoNext", true); disableEnableButtons("#btnResetAll", true); disableEnableButtons("#btnReOpen", true); }
function disableEnableButtons(ctr, isDisable) { $(ctr).prop("disabled", isDisable); }
function ResetTotalBets() { $('[id^="tdTotalBetMeron-"]').each(function () { $(this).html("0"); }); $('[id^="tdTotalBetWala-"]').each(function () { $(this).html("0"); }); $('[id^="tdTotalBetDraw-"]').each(function () { $(this).html("0"); }); }
function ResetPersonalBets() { $('[id^="tdPersonalBetMeron-"]').each(function () { $(this).html("0"); }); $('[id^="tdPersonalBetWala-"]').each(function () { $(this).html("0"); }); $('[id^="tdPersonalBetDraw-"]').each(function () { $(this).html("0"); }); $('#tdPayOutMeron').html('0'); $('#tdPayOutWala').html('0'); $('#lblMeronWinning').html('0'); $('#lblWalaWinning').html('0'); }
function AddWinningPersonalBets() {
    $('[id^="tdPersonalOddId-"]').each(function () {
        var oddId = $(this).html(); var oddName = $("#tdPersonalOdd-" + oddId); var wala = $("#tdPersonalBetWala-" + oddId); var meron = $("#tdPersonalBetMeron-" + oddId); var meronPayout = $("#tdPayOutMeron"); var walaPayout = $("#tdPayOutWala"); var hdBetType = $('#hdBetType').val(); var meronPayoutVal = meronPayout.html(); var walaPayoutVal = walaPayout.html(); if (hdBetType != '0') {
            if (wala.html() != '' || wala.html() != "0") { $('#lblWalaWinning').html(ReplaceNumberWithCommas(RemoveCommaInNumber(wala.html()) * walaPayoutVal / 100)); }
            if (meron.html() != '' || meron.html() != "0") { $('#lblMeronWinning').html(ReplaceNumberWithCommas(RemoveCommaInNumber(meron.html()) * meronPayoutVal / 100)); }
        }
        else { wala.html(ReplaceNumberWithCommas(WinningCalculation(wala.html(), oddName.html(), 'w'))); meron.html(ReplaceNumberWithCommas(WinningCalculation(meron.html(), oddName.html(), 'm'))); }
    });
}
function ResetOwnerName() { }
function displayAdminMessage(message) { $("#adminMessage").text(message); }
function checkIfNameHas(str, searchString) {
    if (str.substring(0, searchString.length) === searchString) { return true; }
    return false;
}
function displayWhoIsOnline(allUsers, username) { }
function SetArenaGameInfo(arenaGameId, gameNumber, meron, wala, meronScore, walaScore) { }
function RemoveCommaInNumber(amount) {
    if (amount != null) {
        if (amount.toString().indexOf(',') > -1) { amount = amount.toString().replace(',', ''); }
        if (amount.toString().indexOf(',') > -1) { amount = amount.toString().replace(',', ''); }
    }
    return amount;
}
$(function () {
    var elem = '<a id="btnHappy" data-message="::happy" class="btn btn-default btn-xs"> <i class="fa fa-smile-o  "></i></a>' +
        '<a id="btnFrown" data-message="::frown" class="btn btn-default btn-xs"> <i class="fa fa-frown-o  "></i></a> ' +
        '<a id="btnHeart"  data-message="::heart" class="btn btn-default btn-xs"> <i class="fa fa-heart  "></i></a>' +
        '<a id="btnThumbsUp" data-message="::thumbsup" class="btn btn-default btn-xs"> <i class="fa fa-thumbs-up  "></i></a>' +
        '<a id="btnThumbsDown" data-message="::thumbsdown" class="btn btn-default btn-xs"> <i class="fa fa-thumbs-down  "></i></a>' +
        '<a id="btnPopcorn" data-message="::popcorn" class="btn btn-default btn-xs"> <i class="fa fa-video-camera "></i></a>' +
        '<a id="btnCoffee"  data-message="::coffee" class="btn btn-default btn-xs"> <i class="fa fa-coffee  "></i></a>'; $('#btnChatIcon').popover({ animation: true, content: elem, html: true });
})
$(document).on("click", "#btnHappy,#btnFrown,#btnHeart,#btnThumbsUp,#btnThumbsDown,#btnCoffee,#btnPopcorn", function (e) { var data = $(this).attr("data-message"); var message = $('#message').val() + ' ' + data + ' '; $('#message').val(message).focus(); $('[data-toggle="popover"]').trigger('click'); }); function copyValueManual(value) { $("#txtBetAmountZero").val(value); }
function resetManualBet() { $("#txtBetAmountZero").val('0'); }
function allInManual() { var pts = $('#lblBalancePoints').html(); $("#txtBetAmountZero").val(RemoveCommaInNumber(pts)); }
function AddMessageTimeStamp(userName, message, timeStamp) {
    var classname = 'text-warning'; var messageClass = ""; if (checkIfNameHas(userName, 'admin')) { classname = 'admin'; messageClass = 'admin'; }
    if (checkIfNameHas(userName, 'csr-')) { classname = 'csr'; messageClass = 'csr'; }
    if (checkIfNameHas(userName, 'admin6')) { classname = 'excpeption'; messageClass = 'excpeption'; }
    if (checkIfNameHas(userName, 'super-')) { classname = 'excpeption'; messageClass = 'admin'; }
    if (checkIfNameHas(userName, 'om-')) { classname = 'excpeption'; messageClass = 'excpeption'; }
    if (checkIfNameHas(userName, 'admin-super')) { classname = 'excpeption'; messageClass = 'excpeption'; }
    if (checkIfNameHas(userName, 'gentleman')) { classname = 'excpeption'; messageClass = 'excpeption'; }
    if (checkIfNameHas(userName, 'csr ')) { classname = 'redOne'; messageClass = 'redOne'; }
    if (message.indexOf("::happy") != -1) { message = message.replace(/::happy/g, " <img src='/img/emo-happy.gif' alt='happy' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::frown") != -1) { message = message.replace(/::frown/g, " <img src='/img/emo-frown.gif' alt='frown' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::heart") != -1) { message = message.replace(/::heart/g, " <img src='/img/emo-heart.gif' alt='heart' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::thumbsup") != -1) { message = message.replace(/::thumbsup/g, " <img src='/img/emo-thumbsup.gif' alt='thumbsup' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::thumbsdown") != -1) { message = message.replace(/::thumbsdown/g, " <img src='/img/emo-thumbsdown.gif' alt='thumbsdown' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::coffee") != -1) { message = message.replace(/::coffee/g, " <img src='/img/emo-coffee.gif' alt='coffee' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::cooking") != -1) { message = message.replace(/::cooking/g, " <img src='/img/emo-cooking.gif' alt='cooking' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::seaman") != -1) { message = message.replace(/::seaman/g, " <img src='/img/emo-seaman.gif' alt='seaman' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::popcorn") != -1) { message = message.replace(/::popcorn/g, " <img src='/img/emo-popcorn.gif' alt='popcorn' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::eating") != -1) { message = message.replace(/::eating/g, " <img src='/img/emo-eating.gif' alt='eating' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::cooking") != -1) { message = message.replace(/::cooking/g, " <img src='/img/emo-cooking.gif' alt='cooking' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::workout") != -1) { message = message.replace(/::workout/g, " <img src='/img/emo-workout.gif' alt='workout' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::hide") != -1) { message = message.replace(/::hide/g, " <img src='/img/emo-hide.gif' alt='hide' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::rope") != -1) { message = message.replace(/::rope/g, " <img src='/img/emo-rope.gif' alt='rope' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::shh") != -1) { message = message.replace(/::shh/g, " <img src='/img/emo-shh.gif' alt='shh' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::emo-coffee") != -1) { message = message.replace(/::emo-coffee/g, " <img src='/img/emoji/emo-coffee.gif' alt='emo-coffee' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::emoji-beer") != -1) { message = message.replace(/::emoji-beer/g, " <img src='/img/emoji/emoji-beer.gif' alt='emo-coffee' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::emo-corn") != -1) { message = message.replace(/::emo-corn/g, " <img src='/img/emoji/emo-corn.gif' alt='emo-corn' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0001") != -1) { message = message.replace(/::0001/g, " <img src='/img/emoji/0001.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0002") != -1) { message = message.replace(/::0002/g, " <img src='/img/emoji/0002.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0003") != -1) { message = message.replace(/::0003/g, " <img src='/img/emoji/0003.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0004") != -1) { message = message.replace(/::0004/g, " <img src='/img/emoji/0004.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0005") != -1) { message = message.replace(/::0005/g, " <img src='/img/emoji/0005.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0006") != -1) { message = message.replace(/::0006/g, " <img src='/img/emoji/0006.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0007") != -1) { message = message.replace(/::0007/g, " <img src='/img/emoji/0007.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0008") != -1) { message = message.replace(/::0008/g, " <img src='/img/emoji/0008.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0009") != -1) { message = message.replace(/::0009/g, " <img src='/img/emoji/0009.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0010") != -1) { message = message.replace(/::0010/g, " <img src='/img/emoji/0010.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0011") != -1) { message = message.replace(/::0011/g, " <img src='/img/emoji/0011.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0012") != -1) { message = message.replace(/::0012/g, " <img src='/img/emoji/0012.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0013") != -1) { message = message.replace(/::0013/g, " <img src='/img/emoji/0013.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0014") != -1) { message = message.replace(/::0014/g, " <img src='/img/emoji/0014.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0015") != -1) { message = message.replace(/::0015/g, " <img src='/img/emoji/0015.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0016") != -1) { message = message.replace(/::0016/g, " <img src='/img/emoji/0016.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0017") != -1) { message = message.replace(/::0017/g, " <img src='/img/emoji/0017.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0018") != -1) { message = message.replace(/::0018/g, " <img src='/img/emoji/0018.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0019") != -1) { message = message.replace(/::0019/g, " <img src='/img/emoji/0019.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0020") != -1) { message = message.replace(/::0020/g, " <img src='/img/emoji/0020.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0021") != -1) { message = message.replace(/::0021/g, " <img src='/img/emoji/0021.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0022") != -1) { message = message.replace(/::0022/g, " <img src='/img/emoji/0022.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0023") != -1) { message = message.replace(/::0023/g, " <img src='/img/emoji/0023.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0024") != -1) { message = message.replace(/::0024/g, " <img src='/img/emoji/0024.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0025") != -1) { message = message.replace(/::0025/g, " <img src='/img/emoji/0025.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0026") != -1) { message = message.replace(/::0026/g, " <img src='/img/emoji/0026.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0027") != -1) { message = message.replace(/::0027/g, " <img src='/img/emoji/0027.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0028") != -1) { message = message.replace(/::0028/g, " <img src='/img/emoji/0028.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0029") != -1) { message = message.replace(/::0029/g, " <img src='/img/emoji/0029.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0030") != -1) { message = message.replace(/::0030/g, " <img src='/img/emoji/0030.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0031") != -1) { message = message.replace(/::0031/g, " <img src='/img/emoji/0031.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0032") != -1) { message = message.replace(/::0032/g, " <img src='/img/emoji/0032.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0033") != -1) { message = message.replace(/::0033/g, " <img src='/img/emoji/0033.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    if (message.indexOf("::0034") != -1) { message = message.replace(/::0034/g, " <img src='/img/emoji/0034.gif' alt='emo' class='img-responsive small img-valign'/> "); }
    message = message.replace(/</g, "&lt;")
    message = message.replace(/&lt;img/g, "<img")
    message = message.replace(/http/g, " ")
    $('#discussion').append('<p class="text-custom-one chat-message  ' + messageClass + '"><strong class="' + classname + '">' + '[' + timeStamp + '] ' + userName + ': </strong>'
        + message + '');
}
function isNumber(evt) {
    evt = (evt) ? evt : window.event; var charCode = (evt.which) ? evt.which : evt.keyCode; var charCode = (evt.which) ? evt.which : evt.keyCode; if (charCode > 31 && (charCode < 48 || charCode > 57)) { return false; }
    return true;
}
function computePayout() {
    var plasada = parseFloat($('#hdPlasadaPercentage').val());

    var totalBetMeron = RemoveCommaInNumber($('#tdTotalBetMeron-1').html());
    var totalBetWala = RemoveCommaInNumber($('#tdTotalBetWala-1').html());
    var meronPayout = 0;
    var walaPayout = 0;
    if (totalBetMeron.length > 0 && totalBetWala.length > 0) {
        if (isNaN(parseInt(totalBetMeron))) { totalBetMeron = 0; }
        if (isNaN(parseInt(totalBetWala))) { totalBetWala = 0; }
        var total = parseInt(totalBetMeron) + parseInt(totalBetWala);
        if (total > 0) {
            if (totalBetMeron > 0) {
                meronPayout = total / parseInt(totalBetMeron);
                meronPayout = meronPayout - (meronPayout * plasada);
                $('#tdPayOutMeron').html((meronPayout * 100).toFixed(1));
                setPayoutBasketball('M');
            }
            if (totalBetWala > 0) {
                walaPayout = total / parseInt(totalBetWala);
                walaPayout = walaPayout - (walaPayout * plasada); $('#tdPayOutWala').html((walaPayout * 100).toFixed(1)); setPayoutBasketball('W');
            }
        }
    }
}

function actualPayout(amount, betOn) {
    amount = RemoveCommaInNumber(amount); if (betOn == 'm') { var payOut = RemoveCommaInNumber($('#tdPayOutMeron').html()); var meronBet = RemoveCommaInNumber($("#tdPersonalBetMeron-1").html()); var total = meronBet * (payOut / 100); $('#lblMeronWinning').html(ReplaceNumberWithCommas(total)); }
    if (betOn == 'w') { var payOut = RemoveCommaInNumber($('#tdPayOutWala').html()); var walaBet = RemoveCommaInNumber($("#tdPersonalBetWala-1").html()); var total = walaBet * (payOut / 100); $('#lblWalaWinning').html(ReplaceNumberWithCommas(total)); }
}
function displayBaccaratResult() {
    var arenaId = getQueryStringValueByKey("aid"); $.ajax({
        url: '/Arena/GetBaccaratResult', type: "GET", data: { aid: arenaId }, dataType: "json", success: function (data) {
            var table = $("#tblBaccaratResult tbody"); var tableAll = $("#tblBaccaratResultAll tbody"); var previousResult = ""; var currentRow = 1; var currentColumn = 1; var currentRowAll = 1; var currentColumnAll = 1; var totalSame = 0; $.each(data, function (idx, elem) {
                if (elem.Result == "W" || elem.Result == "M" || elem.Result == "D" || elem.Result == "C") {
                    var tdValueAll = $("#tdBaccaratAll-" + currentRowAll + "-" + currentColumnAll); tdValueAll.text(elem.FightNumber); if (elem.Result == "W") { tdValueAll.addClass("circleBlueAll"); }
                    if (elem.Result == "M") { tdValueAll.addClass("circleRedAll"); }
                    if (elem.Result == "D") { tdValueAll.addClass("circleGreenAll"); }
                    if (elem.Result == "C") { tdValueAll.addClass("circleCancelAll"); }
                    if (currentRowAll == 7) { currentColumnAll = currentColumnAll + 1; currentRowAll = 1; }
                    else {
                        if (currentColumnAll == 1) { currentRowAll = currentRowAll + 1; currentColumnAll = currentColumnAll; }
                        else { currentRowAll = currentRowAll + 1; currentColumnAll = currentColumnAll; }
                    }
                    if (elem.Result != "C") {
                        if (previousResult == elem.Result) {
                            var tdValue = $("#tdBaccarat-" + currentRow + "-" + currentColumn); if (elem.Result == "W") { tdValue.addClass("circleBlue"); }
                            if (elem.Result == "M") { tdValue.addClass("circleRed"); }
                            if (elem.Result == "D") { tdValue.addClass("circleGreen"); }
                            if (currentRow == 7) { currentColumn = currentColumn + 1; currentRow = 1; }
                            else { currentRow = currentRow + 1; currentColumn = currentColumn; }
                            totalSame = totalSame + 1;
                        }
                        else {
                            if (totalSame > 0) {
                                currentRow = 1; if (totalSame == 7) { currentColumn = currentColumn; }
                                else { currentColumn = currentColumn + 1; }
                            }
                            else { currentColumn = currentColumn; }
                            var tdValue = $("#tdBaccarat-" + currentRow + "-" + currentColumn); if (elem.Result == "W") { tdValue.addClass("circleBlue"); }
                            if (elem.Result == "M") { tdValue.addClass("circleRed"); }
                            if (elem.Result == "D") { tdValue.addClass("circleGreen"); }
                            if (currentRow == 7) { currentColumn = currentColumn + 1; currentRow = 1; }
                            else { currentRow = currentRow + 1; }
                            totalSame = 1;
                        }
                        previousResult = elem.Result; $('#hdPrevousResult').val(previousResult); $('#hdCurrentResultRow').val(currentRow); $('#hdCurrentResultColumn').val(currentColumn);
                    }
                    $('#hdCurrentResultRowAll').val(currentRowAll); $('#hdCurrentResultColumnAll').val(currentColumnAll);
                }
            });
        }, error: function (e) { alert(e.message); }
    });
}
function singleDisplayBaccaratResult(winner, fightNumber) {
    var arenaId = getQueryStringValueByKey("aid"); var previousResult = $('#hdPrevousResult').val(); var currentRow = +$('#hdCurrentResultRow').val(); var currentColumn = +$('#hdCurrentResultColumn').val(); var currentRowAll = +$('#hdCurrentResultRowAll').val(); var currentColumnAll = +$('#hdCurrentResultColumnAll').val(); var totalSame = 0; if (winner == "W" || winner == "M" || winner == "D" || winner == "C") {
        var tdValueAll = $("#tdBaccaratAll-" + currentRowAll + "-" + currentColumnAll); tdValueAll.text(fightNumber); if (winner == "W") { tdValueAll.addClass("circleBlueAll"); }
        if (winner == "M") { tdValueAll.addClass("circleRedAll"); }
        if (winner == "D") { tdValueAll.addClass("circleGreenAll"); }
        if (winner == "C") { tdValueAll.addClass("circleCancelAll"); }
        if (currentRowAll == 7) { currentColumnAll = currentColumnAll + 1; currentRowAll = 1; }
        else {
            if (currentColumnAll == 1) { currentRowAll = currentRowAll + 1; currentColumnAll = currentColumnAll; }
            else { currentRowAll = currentRowAll + 1; currentColumnAll = currentColumnAll; }
        }
        if (winner != 'C') {
            if (previousResult == winner) {
                var tdValue = $("#tdBaccarat-" + currentRow + "-" + currentColumn); if (winner == "W") { tdValue.addClass("circleBlue"); }
                if (winner == "M") { tdValue.addClass("circleRed"); }
                if (winner == "D") { tdValue.addClass("circleGreen"); }
                if (currentRow == 7) { currentColumn = currentColumn + 1; currentRow = 1; }
                else { currentRow = currentRow + 1; currentColumn = currentColumn; }
                totalSame = totalSame + 1;
            }
            else {
                currentRow = 1; currentColumn = currentColumn + 1; var tdValue = $("#tdBaccarat-" + currentRow + "-" + currentColumn); if (winner == "W") { tdValue.addClass("circleBlue"); }
                if (winner == "M") { tdValue.addClass("circleRed"); }
                if (winner == "D") { tdValue.addClass("circleGreen"); }
                currentRow = currentRow + 1; totalSame = 1;
            }
            previousResult = winner; $('#hdPrevousResult').val(previousResult); $('#hdCurrentResultRow').val(currentRow); $('#hdCurrentResultColumn').val(currentColumn);
        }
        $('#hdCurrentResultRowAll').val(currentRowAll); $('#hdCurrentResultColumnAll').val(currentColumnAll);
    }
}
function displayPayout() { }
function setPayoutBasketball(side) {
    var arenaName = $('#lblArenaName').html().toUpperCase().startsWith("AUTO"); if (arenaName == true) {
        if (side == 'M') { $('#tdPayOutMeron').html('190'); }
        if (side == 'W') { $('#tdPayOutWala').html('190'); }
    }
}