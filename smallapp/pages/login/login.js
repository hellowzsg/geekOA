// pages/login/login.js
const util = require("../../utils/util.js")
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    account: '',
    password: '',
    background: 'data:image/png;base64,' + util.base64('/images/geek100.png'),
  },
  bindInputAct: function(e) {
    this.setData({
      account: e.detail.value
    })
  },
  bindInputPwd: function(e) {
    this.setData({
      password: e.detail.value
    })
  },
  geekLogin: function() {
    let that = this;
    if (that.data.account === '') {
      wx.showToast({
        title: '请输入用户名',
        icon: 'none'
      })
    } else if (that.data.account !== '' && that.data.password === '') {
      wx.showToast({
        title: '请输入密码',
        icon: 'none'
      })
    } else {
      wx.login({
        success: res => {
          // console.log(res)
          if (res.code) {
            util.requestPost({
              url: '/api/Wx/login',
              data: {
                code: res.code,
                username: that.data.account,
                password: that.data.password,
                isfirst: 2
              },
              header: {
                "content-type": 'application/x-www-form-urlencoded'
              }
            }).then(function(res) {
              app.globalData.userInfo.token = res.data.data.token
              app.globalData.userInfo.uId = res.data.data.uid
              wx.showToast({
                title: res.data.msg,
              })
              setTimeout(function() {
                wx.switchTab({
                  url: '/pages/journal/journal',
                })
              }, 500)
            }, function(res) {
              wx.showToast({
                title: res.data.msg,
                icon: 'none'
              })
            })
          } else {
            console.log('获取code失败！' + res.errMsg)
          }
        }
      })
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})