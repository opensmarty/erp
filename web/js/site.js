/**
 * Created by Renk on 2016/5/5.
 *
 */
Yiipal = {};
Yiipal.AjaxCommands = function () {};
Yiipal.AjaxCommands.prototype = {
    refresh: function (command) {
        location.href = location.href;
    },
    redirect:function(command){
        location.href = command.url;
    },
    modal:function(command){
        $('#global-ajax-modal').modal('show');
        $('#global-ajax-modal .modal-content').html(command.data);
        $('#global-ajax-modal').on('hidden.bs.modal', function () {
            location.href = location.href;
        })
    }
};

/**
 * 初始化时间控件
 */
var init_date_range_input = function(){
    $('input[name="daterange"], .form-daterange').daterangepicker({
        autoUpdateInput: false,
        format: 'YYYY-MM-DD',
        locale: {
            applyLabel: '确认',
            cancelLabel: '取消',
            fromLabel: '从',
            toLabel: '到',
            weekLabel: 'W',
            customRangeLabel: '选择时间',
            daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
            monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"]
        },
        ranges: {
            "今天": ["'" + moment().format('L') + "'", new Date()],
            "三天": ["'" + moment().subtract(2, 'days').format('L') + "'", new Date()],
            "本周": ["'" + moment().day("Monday").format('L') + "'", new Date()],
            "本月": ["'" + moment().date(1).format('L') + "'", new Date()]
        }
    }).on('apply.daterangepicker', function (v, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + '/' + picker.endDate.format('YYYY-MM-DD'));
        if($(".grid-view").length>0)
            $(".grid-view").yiiGridView('applyFilter');
    }).on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
        if($(".grid-view").length>0)
            $(".grid-view").yiiGridView('applyFilter');
    });
};

$(document).ready(function () {
    var ajaxCommands = new Yiipal.AjaxCommands();
    //全局Ajax loading 设置.
    $(document).ajaxStart(function (event) {
        var $el = $(event.target.activeElement)
        $(".overlay").show();
        if(!$el.hasClass('keep-modal')){
            $('.modal').modal('hide');
        }
    });

    $(document).ajaxComplete(function () {
        $(".overlay").hide();
    });

    $(document).ajaxError(function () {
        $(".overlay").hide();
    });

    //表单提交动作防连击
    $('form').submit(function () {
        $(".overlay").show();
    });
    bootbox.setLocale("zh_CN");
    //操作确认提醒
    $(document).on('click', '.confirm', function (event) {
        var event = $(this).text();
        if (!window.confirm('你确认进行【'+event+'】操作吗？')) {
            return false;
        }
    });

    //分页控制，每页数目
    $(document).on('change', 'select#per-page', function (event) {
        window.location.href = $(this).find("option:selected").attr("target");
    });

    //切换侧边栏
    $(document).on('click', 'a.sidebar-toggle', function () {
        if ($("body").hasClass('sidebar-collapse')) {
            Cookies.set('sidebar-collapse', 1);
        } else {
            Cookies.set('sidebar-collapse', 0);
        }
    });

    /**
     * 获取选中的id编号
     */
    $(document).on('click', 'button[data-action-before=get_ids]', function () {
	    $(this).parents('form').attr('action',$(this).parents('form').attr('action')+'?'+$('#searchForm').serialize());
        var ids = $('.grid-view,.grid-view2').yiiGridView('getSelectedRows');
        $(this).parents('form').find('#ids').val(ids.join());
    });

    /**
     * 时间段控件
     */
    init_date_range_input();

    /**
     * 时间控件
     */
    $('input.single-date, input.form-datesingle').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        todayHighlight:true,
        locale: {
            format: 'YYYY-MM-DD',
            daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
            monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"]
        }
    }).on('apply.daterangepicker', function (v, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
        if($(".grid-view").length>0)
            $(".grid-view").yiiGridView('applyFilter');
    }).on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
        if($(".grid-view").length>0)
            $(".grid-view").yiiGridView('applyFilter');
    });


    /**
     * ajax Form处理
     */
    $(document).on('submit','form.ajax-form',function(){
        var options = {
            target: '',
            success: function(data,status, event,target){
                if (data.status === '00') {
                    if ($(target).hasClass('download') && data.data) {
                        $.fileDownload(data.data);
                    }
                    var targetBtn = $(target).find('button[type=submit]');
                    var nextUrl = targetBtn.data('next-url');
                    //如果有下一步操作
                    if(targetBtn.data('next-url')){
                        $.get(targetBtn.data('next-url'),function(data,status,xhr){
                            var ct = xhr.getResponseHeader("content-type") || "";
                            if (ct.indexOf('html') > -1) {
                                //$('.modal').modal('hide');
                                $('#global-ajax-modal').modal('show');
                                $('#global-ajax-modal .modal-content').html(data);
                            }else{
                                if (data.status === '00') {
                                    $('.modal').modal('hide');
                                    bootbox.alert(data.msg, function() {
                                        //location.reload();
                                        ajaxCommands[data.command.method](data.command);
                                        //location.href=location.href;
                                    });
                                } else {
                                    bootbox.alert(data.msg);
                                    return false;
                                }
                            }

                        });
                    //没有下一步操作
                    }else{
                        $('.modal').modal('hide');
                        bootbox.alert(data.msg, function() {
                            ajaxCommands[data.command.method](data.command);
                            //location.href=location.href;
                        });
                    }
                } else {
                    bootbox.alert(data.msg);
                }
                return false;
            }
        };
        $(this).ajaxSubmit(options);
        return false;
    });

    /**
     * 倒计时处理
     */
    $(".countdown").each(function () {
        var date = $(this).text();
        $(this).countdown(date, {elapse: true})
            .on('update.countdown', function (event) {
                var $this = $(this);
                if (event.elapsed) {
                    $this.html(event.strftime('<span class="delivery-countup">超过交货时间: %D 天 %H:%M:%S</span>'));
                } else {
                    $this.html(event.strftime('<span class="delivery-countdown">距离交货时间：%D 天 %H:%M:%S</span>'));
                }
            });
    });


    /**
     * 获取URL参数
     * @param param
     * @returns {*}
     */
    function $_GET(param,url) {
        if(url==undefined){
            url=window.location.href;
        }
        var vars = {};
        url.replace( location.hash, '' ).replace(
            /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
            function( m, key, value ) { // callback
                vars[key] = value !== undefined ? value : '';
            }
        );

        if ( param ) {
            return vars[param] ? vars[param] : '';
        }
        return vars;
    }


    /**
     * 列表页的下拉操作项,ajax请求
     */
    $(document).on('click','a.ajax',function(){
        var url = $(this).attr("href");
        var id = $_GET('id',url);
        var text = $(this).text();
        var target = $(this);
        bootbox.confirm("你确定要【"+text+"】吗？", function(result) {
            if(result == false){
                return true;
            }
            $.post(url,{ids:id},function(data){
                if (data.status === '00') {
                    if ($(target).hasClass('download') && data.data) {
                        $.fileDownload(data.data);
                    }
                    bootbox.alert(data.msg, function() {
                        ajaxCommands[data.command.method](data.command);
                    });
                } else {
                    bootbox.alert(data.msg);
                }
            });
        });
        return false;
    });


    /**
     * 全局备注
     */
    $(document).on("click",'table .btn-group .dropdown-menu a.ajax-comment',function(){
        var url = $(this).attr("href");
        $.get(url,function(html){
            $('#global-ajax-modal').modal('show');
            $('#global-ajax-modal .modal-content').html(html);
        });
        return false;
    });

    //带评论的链接
    $(document).on("click",'a.ajax-with-comment',function(){
        var url = $(this).attr("href");
        var data = $(this).attr('data');
        data = jQuery.parseJSON(data);
        var alert_msg = data.alert_msg;
        var commentUrl = data.commentUrl;

        if(alert_msg =="" || alert_msg == undefined){
            $.get(commentUrl,function(html){
                $('#global-ajax-modal').modal('show');
                $('#global-ajax-modal .modal-content').html(html);
                $('#global-ajax-modal .modal-content button[type=submit]').data('next-url',url);
            });
        }else{
            bootbox.confirm(alert_msg,function(result) {
                if(result == false){
                    return true;
                }
                $.get(commentUrl,function(html){
                    $('#global-ajax-modal').modal('show');
                    $('#global-ajax-modal .modal-content').html(html);
                    $('#global-ajax-modal .modal-content button[type=submit]').data('next-url',url);
                });
            });
        }

        //$.get(commentUrl,function(html){
        //    $('#global-ajax-modal').modal('show');
        //    $('#global-ajax-modal .modal-content').html(html);
        //    $('#global-ajax-modal .modal-content button[type=submit]').data('next-url',url);
        //});
        return false;
    });

    /**
     * 全局弹出加载
     */
    $(document).on("click",'a.ajax-modal',function(){
        var url = $(this).attr("href");
        var title = $(this).text();
        if($(this).attr("title")!=undefined){
            title = $(this).attr("title");
        }
        $.get(url,function(html){
            $('#global-ajax-modal').modal('show');
            $('#global-ajax-modal .modal-title').text(title);
            $('#global-ajax-modal .modal-content-body').html(html);
        });
        return false;
    });

    /**
     * 编辑输入框
     */
    $('a.editable-text').editable();
    /**
     * 只能编辑一次
     */
    $('a.editable-once-text').editable({
        success: function(response, newValue) {
            if(response.status == '00'){
                $(this).replaceWith(newValue);
            }
        }
    });

    $(document).on('click','.order-index table tbody tr',function(){
        $(this).siblings().removeClass("tr-bg-highlight");
        $(this).addClass("tr-bg-highlight");
    });

    /**
     * 点击备注后，备注变绿色，表示已读
     */
    $(document).on('click','.click-green',function(){
        $(this).parent().removeClass("label-danger").addClass("label-success");
    });

    $(document).on('click','.color-picker',function(){
        var target_id = $(this).data("id");
        var url = '/api/ajax/get-color-card';
        $.get(url,function(html){
            $('#global-ajax-modal').find(".modal-dialog").css("width","60%").end().modal('show');
            $('#global-ajax-modal .modal-content').html(html);
            $('#global-ajax-modal .modal-content').find("#target_select_id").val(target_id);
        });
        return false;
    });


//以上为全局js-----------------------
//以下为业务js-----------------------

    /**
     * 修改物流公司
     */
    $('a.edit-shipping-method').editable({
        source: [
            {value: 'DHL', text: 'DHL'},
            {value: 'UPS', text: 'UPS'},
            {value: 'EUB', text: 'EUB'},
            {value: 'ARAMEX', text: 'ARAMEX'}
        ]
    });
    
    $('a.edit-is-clean').editable({
        source: [
            {value: '0', text: '否'},
            {value: '1', text: '是'},
        ]
    });

    $('.grid-view input[type=checkbox]').shiftcheckbox();
});
