// pages/check/checkrecord/explain/explain.js
const app = getApp()
const util = require("../../../../utils/util.js")
const time = util.formatTime(new Date())
const date = [time.year, time.month, time.day].map(util.formatNumber).join("-")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    remark: '',
    remarkNow: '',
    date: '',
    timae: '',
    aid: '',
    uid: '',
    isShow: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(opts) {
    console.log(opts)
    var that = this
    that.setData({
      date: opts.date,
      time: opts.absence,
      aid: opts.aid,
      uid: opts.uid,
      remark: opts.remark,
      remarkNOw: opts.remark,
    })
    if (opts.uid == app.globalData.userInfo.uId && opts.date === date) {
      that.setData({
        isShow: true
      })
    }
  },

  modifyInput: function(e) {
    var that = this;
    that.setData({
      remarkNow: e.detail.value
    })
    if (that.data.remarkNow !== that.data.remarkOld) {
      that.setData({
        isSave: true
      })
    }
  },

  save: function(e) {
    var that = this;
    var remarkNow = that.data.remarkNow,
      remarkOld = that.data.remark;
    if (remarkNow !== null && remarkNow !== undefined && remarkNow !== '') {
      if (remarkNow !== remarkOld) {
        util.requestPost({
          url: '/api/Wx/absence',
          data: {
            aid: that.data.aid,
            content: that.data.explain,
            access_token: app.globalData.userInfo.token
          }
        }).then(function(res) {
          wx.showToast({
            title: res.data.msg,
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
      }
    } else {
      wx.showToast({
        title: '内容不能为空',
        icon: 'none'
      })
    }
  },
})