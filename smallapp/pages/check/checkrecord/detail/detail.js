const app = getApp()
const util = require("../../../../utils/util.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    details: [],
    current: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var that = this
    util.requestGet({
      url: "/api/Wx/detail",
      data: {
        aid: options.aid,
        access_token: app.globalData.userInfo.token
      },
    }).then(function(res) {
      that.setData({
        details: res.data.data,
        current: res.data.data.length - 1
      })
    }, function(res) {
      wx.showToast({
        title: res.data.msg,
        icon: "none"
      })
    }).catch(function(res) {
      console.log(res)
    })
  },
})