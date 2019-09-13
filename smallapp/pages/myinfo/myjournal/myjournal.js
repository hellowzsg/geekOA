// pages/myinfo/myjournal/myjournal.js
const util = require("../../../utils/util.js")
const app = getApp()
Page({
  data: {
    time: {},
    content: "",
    boxBackground: 'data:image/png;base64,' + util.base64('/images/journal_bg.png'),
    boxHeight: '',
    winHeight: '',
    formHeight: '',
    autoFocus: true
  },
  onLoad: function(options) {
    var date,
      that = this,
      time = util.formatTime(new Date());
    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          time: time,
          winHeight: res.windowHeight * 2,
          boxHeight: res.windowHeight * 2 - 182 - 150
        })
      },
    })
  },
  onShow: function(options) {
    var that = this
  },
  onUnload: function(options) {

  },
  formSubmit: function(e) {
    var that = this,
      time = util.formatTime(new Date()),
      date = [time.year, time.month, time.day].map(util.formatNumber).join("-")
    wx.showLoading({
      title: '提交中',
    })
    wx.getSystemInfo({
      success(res) {
        if (res.platform === "devtools") {
          e.detail.formId = ''
        }
      }
    })
    util.requestPost({
      url: '/api/Wx/addLog/formid',
      data: {
        access_token: app.globalData.userInfo.token,
        formid: e.detail.formId,
        content: that.data.content
      },
    }).then(function(res) {
      wx.showToast({
        title: '添加成功',
        icon: 'success',
        success: function() {
          setTimeout(function() {
            wx.navigateBack({
              delta: 1
            })
          }, 1000)
        }
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
  bindHeight: function(e) {
    var that = this
    that.setData({
      boxHeight: +e.detail.heightRpx > that.data.winHeight - 182 - 150 ? +e.detail.heightRpx : +that.data.winHeight - 182 - 150,
      formHeight: +e.detail.heightRpx > that.data.winHeight - 182 - 150 ? +e.detail.heightRpx + 150 + 182 : that.data.winHeight + 50
    })
  },
  textareaFun: function(e) {
    var that = this
    that.setData({
      content: e.detail.value
    })
  }
})