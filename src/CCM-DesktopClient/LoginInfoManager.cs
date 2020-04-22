using BlazorDesktopClient.Models;

namespace BlazorDesktopClient
{
    public class LoginInfoManager
    {
        private LoginInfo? loginInfo;
        public LoginInfo? GetLoginInfo()
        {
            return loginInfo;
        }

        public void SetLoginInfo(LoginInfo loginInfo)
        {
            this.loginInfo = loginInfo;
        }

        public void NewLoginInfo()
        {
            this.SetLoginInfo( new LoginInfo());
        }
    }
}