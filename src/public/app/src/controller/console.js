layui.define(function(exports){
    //区块轮播切换
    layui.use(['admin', 'carousel'], function(){
        var $ = layui.$,
            admin = layui.admin,
            carousel = layui.carousel,
            element = layui.element,
            device = layui.device();

        //轮播切换
        $('.layadmin-carousel').each(function(){
            var othis = $(this);
            carousel.render({
                elem: this,
                width: '100%',
                arrow: 'none',
                interval: othis.data('interval'),
                autoplay: othis.data('autoplay') === true,
                trigger: (device.ios || device.android) ? 'click' : 'hover',
                anim: othis.data('anim')
            });
        });
    });
    exports('console', {})
});