if(typeof console != 'object'){ var console = {'log': function(){}}; } // hehe

// config
var HadogunEffectFadeIn = 2000;
var HadogunEffectFadeOut = 2000;
var HadogunEffectTimeout = 5000;
var HadogunServerHostName = "hadoch.cfe.jp:8080";

var hadogunEffect = {
    template : null,
    init : function(){
        this.template = $('#hadogun_template').html();
        Mustache.parse(this.template);

    },
    fire : function(message){
        message = maxim.getRand();

        var html = Mustache.render(this.template, message);

        $.blockUI({
            message: html,
            overlayCSS:  {
                backgroundColor: '#FFF',
                opacity:         0.99
            },
            css: {
                fontSize:      '20pt',
                fontFamily:     'Hiragino Mincho ProN',
                fontWeight: 'bold',
                padding:        0,
                margin:         0,
                width:          '80%',
                top:            '30%',
                left:           '10%',
                textAlign:      'center',
                color:          '#000',
                border:         '',
                backgroundColor:'',
                cursor:         'wait'
            },
            fadeIn:  HadogunEffectFadeIn,
            fadeOut:  HadogunEffectFadeOut
        });

        setTimeout(function() {
            $.unblockUI();
        }, HadogunEffectTimeout);
    }
};

var tweet = {
    name : "-",
    img_url : "-",
    text : "-",
    url : "-",
    date : "-",
    create : function(screen_name, avatar_img_url, tweet_text, date_str, tweet_url){
        this.name = screen_name;
        this.img_url = avatar_img_url;
        this.text = tweet_text;
        this.date = date_str;
        this.url = tweet_url;
        return this;
    }
};

var tweet_feeder = {
    $tweet_list : null,
    $sspk : null,
    $lspk : null,
    tweet_template: null,

    init : function(){
        // 毎回探さないように
        this.$tweet_list = $("#tweet-list");
        this.$sspk = $("#sspk");
        this.$lspk = $("#lspk");

        // TweetテンプレートのPrepare
        this.tweet_template = $('#template').html();
        Mustache.parse(this.tweet_template);
    },
    addTweet : function(tweet){
        // 一件表示に
        this.$tweet_list.empty();
        //return;
        var tweet_elm_str = Mustache.render(this.tweet_template, tweet);
        var tweet_elm = $(tweet_elm_str);

        // スライドしながら表示させたいぞい (重い…
        //tweet_elm.hide();
        $(tweet_elm).transition({ scale: 3, opacity: 0 },0);

        this.$tweet_list.prepend(tweet_elm);
        //tweet_elm.slideDown();
        $(tweet_elm).transition({ scale: 1, opacity: 1  },200);
        //$(tweet_elm).transition({
        //    perspective: '1000px',
        //    rotateX: '360deg'
        //});

        // spk
        this.$sspk.transition({ scale: 0.995 },0);
        this.$sspk.transition({ scale: 1 },10);

        this.$lspk.transition({ scale: 1.01 },0);
        this.$lspk.transition({ scale: 1 },10);

        // 無駄に多くなるので消し込み
        // Vueつかいたいけど、仕変を考えると…
        //var tweet_elm_list = this.$tweet_list.children('li');
        //if(tweet_elm_list.length>20){
        //    $(tweet_elm_list.get(tweet_elm_list.length-1)).remove();
        //}
    },
    reset : function(){
    }
};

var connection = {
    conn: null,
    reconnectWait: 10000,
    init: function() {
        console.log('Init connection.');
        connection.createSession();
    },
    onOpen:
        function() {
            console.log('connection success.');
            connection.conn.subscribe('hadoch', function(topic, data) {
                if(data.type=='hadogun_fire'){
                    console.log('fire!');
                    hadogunEffect.fire();
                }

                tweet_feeder.addTweet(
                    tweet.create(
                        data.tweet.screen_name,
                        data.tweet.profile_image_url,
                        data.tweet.text,
                        '1999-12-31',
                        'https://twitter.com/'+data.tweet.screen_name+'/status/'+data.tweet.id_str
                    )
                );
            });
        }
    ,
    onClose:function(){
        console.log('WebSocket connection closed');
        connection.disposeSession();
        //再接続
        console.log('Retry setup.');
        setTimeout(
            connection.createSession,
            connection.reconnectWait
        );
    },
    createSession:function(){
        console.log('Try connecting to server.');
        connection.conn = new ab.Session(
            'ws://'+HadogunServerHostName,
            connection.onOpen,
            connection.onClose,
            {'skipSubprotocolCheck': true}
        );
    },
    disposeSession:function(){
        connection.conn = null;
    }
};

$(function(){
    tweet_feeder.init();
    hadogunEffect.init();
    connection.init();
});
