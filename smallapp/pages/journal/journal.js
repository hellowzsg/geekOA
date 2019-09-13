var util = require("../../utils/util.js")
const app = getApp()
const time = util.formatTime(new Date())
const dateNow = [time.year, time.month, time.day].map(util.formatNumber).join("-")
Page({
  data: {
    dateLS: '',
    isModify: false,
    isJournal: false,
    logList: [],
    currentLid: -1,
    myCountent: '',
    myUid: ''
  },
  onLoad: function(options) {
    var that = this;
    that.setData({
      myUid: app.globalData.userInfo.uId,
    })
    //模拟传参
    var event = {
      detail: {}
    };
    if (that.data.dateLS !== '') {
      event.detail.date = that.data.dateLS;
    } else {
      event.detail.date = dateNow;
    }
    that.request(event);
  },
  onPullDownRefresh: function() {
    var that = this;
    //模拟传参
    var event = new Object();
    event.detail = new Object();
    if (that.data.dateLS !== '') {
      event.detail.date = that.data.dateLS;
    } else {
      event.detail.date = dateNow;
    }
    that.request(event);
    wx.stopPullDownRefresh();
  },
  onHide: function(options) {
    this.setData({
      isModify: false,
    })
  },
  request: function(e) {
    //e为组件传入的事件参数,要做判断,用于解决页面展示时无参情况
    var that = this;
    that.setData({
      dateLS: e.detail.date,
    })
    util.requestGet({
      url: '/api/Wx/log',
      data: {
        date: e.detail.date,
        access_token: app.globalData.userInfo.token
      }
    }).then(function(res) {
      //处理一下空白
      res.data.data.logs.forEach(function(value, index, item) {
        item[index].content = item[index].content.trim()
      })
      //找到我自己
      let me = res.data.data.logs.find(function(item) {
        if (item.uid === app.globalData.userInfo.uId)
          return item
      })
      that.setData({
        logList: res.data.data.logs,
        myContent: me ? me.content : '',
        isJournal: false
      })
    }, function(res) {
      that.setData({
        logList: [],
        isJournal: true
      })
      wx.showToast({
        title: res.data.msg,
        icon: "none",
      })
    }).catch(function(err) {
      console.log(err)
    })
  },
  //展开
  seeDetail: function(e) {
    if (this.data.currentLid !== e.currentTarget.dataset.lid) {
      this.setData({
        currentLid: e.currentTarget.dataset.lid
      })
    } else {
      this.setData({
        currentLid: -1
      })
    }
  },
  //修改日志输入数据绑定
  modifyInput: function(e) {
    this.setData({
      myContent: e.detail.value
    })
  },
  //按钮样式切换
  modifyBtn: function() {
    this.setData({
      isModify: true
    })
  },
  //保存日志
  saveLog: function(e) {
    let that = this
    util.requestPost({
      url: '/api/Wx/editLog',
      data: {
        content: that.data.myContent,
        lid: e.currentTarget.dataset.lid,
        access_token: app.globalData.userInfo.token
      }
    }).then(function(res) {
      wx.showToast({
        title: res.data.msg,
        success: function() {
          setTimeout(function() {
            //模拟传参
            var event = {
              detail: {}
            };
            if (that.data.dateLS !== '') {
              event.detail.date = that.data.dateLS;
            } else {
              event.detail.date = dateNow;
            }
            that.request(event);
          }, 500)
        }
      })
      that.setData({
        isModify: false,
      })
    }, function(res) {
      wx.showToast({
        title: res.data.msg,
        icon: 'none'
      })
    }).catch(function(res) {
      console.log(res)
    })
  },
  //取消修改
  cancelLog: function(e) {
    let that = this
    wx.showModal({
      title: '提示',
      content: '确定放弃当前修改嘛',
      success: function(res) {
        if (res.confirm) {
          that.setData({
            isModify: false,
            myContent: e.currentTarget.dataset.content
          })
        }
      }
    })
  }
})