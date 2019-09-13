 App({
   onLaunch: function(e) {
     var that = this;
     wx.login({
       success: res => {
         // 发送 res.code 到后台换取 openId, sessionKey, unionId
         if (res.code) {
           wx.request({
             url: that.globalData.urlId + '/api/Wx/login',
             data: {
               code: res.code,
             },
             header: {
               'Content-Type': 'application/json',
             },
             method: 'GET',
             success: function(res) {
               if (res.data.data.isfirst) {
                 wx.redirectTo({
                   url: '/pages/login/login',
                 })
               } else {
                 if (res.data.code == 0) {
                   that.globalData.userInfo.token = res.data.data.token;
                   that.globalData.userInfo.uId = res.data.data.uid;
                   if (e.path) {
                     wx.switchTab({
                       url: '/pages/journal/journal',
                     })
                   }
                 } else {
                   wx.showToast({
                     title: '登录失败，请重试',
                     icon: 'none'
                   })
                 }
               }
             },
           })
         } else {
           console.log('获取code失败！' + res.errMsg)
         }
       }
     })
   },
   globalData: {
     userInfo: {
       token: '',
       uId: '',
       lastWebViewTime: '',
       device: {
         network: ''
       }
     },
     urlId: 'https://geek.heeyboy.cn',
     uId: ''
   }
 })