// config
var HadogunEffectFadeIn = 2000;
var HadogunEffectFadeOut = 2000;
var HadogunEffectTimeout = 5000;

var hadogunEffect = {
    template : null,
    init : function(){
        this.template = $('#hadogun_template').html();
        Mustache.parse(this.template);

    },
    fire : function(message){
        message = maxim.getRand();

//        {"text":"凄い感じのテキストが", "author":"うずら"};
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
        this.date = moment(date_str);
        this.url = tweet_url;
        return this;
    }
};

var tweet_feeder = {
    $tweet_list : null,
    tweet_template: null,

    init : function(){
        // 毎回探さないように
        this.$tweet_list = $("#tweet-list");
        // TweetテンプレートのPrepare
        this.tweet_template = $('#template').html();
        Mustache.parse(this.tweet_template);
    },
    addTweet : function(tweet){
        //return;
        var tweet_elm_str = Mustache.render(this.tweet_template, tweet);
        var tweet_elm = $(tweet_elm_str);

        // スライドしながら表示させたいぞい
        tweet_elm.hide();
        this.$tweet_list.prepend(tweet_elm);
        tweet_elm.slideDown();

        // 無駄に多くなるので消し込み
        // Vueつかいたいけど、仕変を考えると…
        var tweet_elm_list = this.$tweet_list.children('li');
        if(tweet_elm_list.length>100){
            $(tweet_elm_list.get(tweet_elm_list.length-1)).remove();
        }
    },
    reset : function(){
    }
};

$(function(){
    tweet_feeder.init();
    hadogunEffect.init();

    var conn = new ab.Session('ws://localhost:8080',
        function() {
            conn.subscribe('hadoch', function(topic, data) {
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
                        'https://twitter.com/uzulla/status/511037414563774464'
                    )
                );
            });
        },
        function() {
            //再接続を書くぞ
            console.warn('WebSocket connection closed');
        },
        {'skipSubprotocolCheck': true}
    );
});
