//======================================================================
// トップページ用
// 次のターンまでの時間
function remainTime(nextTime) {
    var now = new Date();
    var remain = nextTime - Math.floor(now / 1000);
    var hour;
    var min;
    var sec;    
    
    if (remain < 0) {
        hour = "00";
        min = "00";
        sec = "00";
    } else {
        hour = Math.floor(remain / 3600);
        min  = Math.floor(remain % 3600 / 60);
        sec  = Math.floor(remain % 3600 % 60);
        
        if (min < 10) {
            min = "0" + min;
        }
        if (sec < 10) {
            sec = "0" + sec;
        }
    }
    document.write(hour + ' 時間 ' + min + ' 分 ' + sec + ' 秒)');
}

//======================================================================
// 開発画面用
//======================================================================
// 開発、観光画面
function Navi(position, img, title, pos, text, exp) {

    var StyElm = document.getElementById("NaviView");
    StyElm.style.visibility = "visible";

    var posx = pos.indexOf(",");
    var posy = pos.indexOf(")");
    var x = pos.substring(1, posx);
    var winEvent = windowEvent();

    if (position == 1) {
        // right
        StyElm.style.marginLeft = (x - 19) * 32 + 478;
        StyElm.style.top = document.body.scrollTop + winEvent.clientY + 150;
    } else {
        // left
        StyElm.style.marginLeft = (x - 19) * 32 + 668;
        StyElm.style.top = document.body.scrollTop + winEvent.clientY + 150;
    }

    StyElm.innerHTML = "<table><tr><td class='M'><img class='NaviImg' src=" + img + "></td><td class='M'><div class='NaviTitle'>" + title + " " + pos + "<\/div><div class='NaviText'>" + text.replace("\n", "<br>") + "</div></td></tr></table>";
    if (exp) {
        StyElm.innerHTML += "<div class='NaviText'>" + eval(exp) + "<\/div>";
    }
}

function NaviClose() {
    var StyElm = document.getElementById("NaviView");
    StyElm.style.visibility = "hidden";
}

function windowEvent(){
    if (window.event) {
        return window.event;
    }
    var caller = arguments.callee.caller;
    while (caller) {
        var ob = caller.arguments[0];
        if (ob && ob.constructor == MouseEvent) {
            return ob
        }
        caller = caller.caller;
    }
    return null;
}
