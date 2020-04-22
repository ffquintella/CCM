using Microsoft.AspNetCore.Components;
using Services.Authentication;

namespace Services.Security
{
    public class RoleManager
    {

        public RoleManager(LoginService loginService)
        {
            LoginService = loginService;
        }
        
        private LoginService LoginService { get; set; }

        public bool UserIsInRole(string role)
        {
            if (LoginService.IsLoggedIn)
            {
                var roles = LoginService.AuthenticationData.Roles;
                if (roles.Contains(role)) return true;
            }

            return false;
        }
    }
}