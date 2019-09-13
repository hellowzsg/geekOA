const app = getApp()
const util = require("../../../utils/util.js")
Page({
  data: {
    begin: '',
    end: '',
    rank: [],
    ranking: '',
    cha: '',
    currentSelect: ''
  },
  onLoad: function(options) {
    var date,
      that = this,
      time = util.formatTime(new Date());
    date = [time.year, time.month, time.day].map(util.formatNumber).join("-")
    that.setData({
      begin: util.formatBegin(new Date()),
      end: date
    })
    that.request()
  },

  request: function() {
    var that = this
    util.requestGet({
      url: '/api/Wx/rank',
      data: {
        begin: that.data.begin,
        end: that.data.end,
        access_token: app.globalData.userInfo.token
      }
    }).then(function(res) {
      let my = res.data.data.users.find(function(item) {
        if (item.uid === app.globalData.userInfo.uId)
          return item
      })
      that.setData({
        rank: res.data.data.users,
        ranking: my ? my.num : ''
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

  bindDateChangeBegin: function(e) {
    let that = this
    that.setData({
      begin: e.detail.value
    })
    that.request()
  },

  bindDateChangeEnd: function(e) {
    let that = this
    that.setData({
      end: e.detail.value
    })
    that.request()
  },

  rank: function(e) {
    var that = this
    if (e.currentTarget.dataset.name != that.data.currentSelect) {
      util.requestGet({
        url: '/api/Wx/rank',
        data: {
          time: e.currentTarget.dataset.name,
          access_token: app.globalData.userInfo.token
        },
      }).then(function(res) {
        let my = res.data.data.users.find(function(item) {
          if (item.uid === app.globalData.userInfo.uId)
            return item
        })
        that.setData({
          rank: res.data.data.users,
          ranking: my ? my.num : '',
          begin: res.data.data.start,
          end: res.data.data.end,
          currentSelect: e.currentTarget.dataset.name
        })
      }, function(res) {
        wx.showToast({
          title: res.data.msg,
          icon: "none"
        })
      }).catch(function(res) {
        console.log(res)
      })
    } else {
      wx.showToast({
        title: '你排第几呢？',
        icon: 'none'
      })
    }
  }
})