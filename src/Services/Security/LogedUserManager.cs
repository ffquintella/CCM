using Microsoft.AspNetCore.Components;

namespace Services.Security
{
    public class LogedUserManager
    {
        
        private RoleManager RoleManager { get; set; }

        public LogedUserManager(RoleManager roleManager)
        {
            RoleManager = roleManager;
        }

        public bool IsAdministrator
        {
            get { return RoleManager.UserIsInRole("Administrator"); }
        }
        
    }
}