using Domain.Authentication;
using MatBlazor;
using Microsoft.AspNetCore.Components;
using Services.Authentication;
using CCM_WebClient.Translation;

namespace CCM_WebClient.Models
{
    public class LoginPanelModel: BaseModel
    {
        
        [Inject] public LoginService LoginService { get; set; }
        

        public string login;
        public string password;

        public bool ShowDialog
        {
            get { return !LoginService.IsLoggedIn; }
            set { LoginService.IsLoggedIn = !value; }
        }

        public void TryLogin(string login, string password)
        {
            LoginService.ExecuteLogin(login, password);

            if (!LoginService.IsLoggedIn)
            {
                if (LoginService.AuthenticationData != null)
                {
                    switch (LoginService.AuthenticationData.ErrorType)
                    {
                        case AuthenticationErrorType.BadPassword:
                            ShowWarning("Login Invalid", "Account or password invalid. Code:101");
                            break;
                        case AuthenticationErrorType.LoginDoesntExists:
                            ShowWarning("Login Invalid", "Account or password invalid. Code:102");
                            break;
                        case AuthenticationErrorType.RequestBadFormated:
                            ShowWarning("Login Invalid", "Request bad formated. Code:103");
                            break;
                        case AuthenticationErrorType.UnkwonError:
                            ShowWarning("Login Invalid", "Unkown login error. Code:104");
                            break;
                    }
                }
                else
                {
                    // if we got here we have an invalid request
                    ShowWarning("Login Invalid", "Your login failed due to unkown conditions.");
                }
            }
            StateHasChanged();

        }
        
        
    }
}