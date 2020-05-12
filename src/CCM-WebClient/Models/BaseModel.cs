using MatBlazor;
using Microsoft.AspNetCore.Components;
using Services.Security;

namespace CCM_WebClient.Models
{
    public class BaseModel: ComponentBase
    {
        [Inject] LogedUserManager LogedUserManager { get; set; }

        public bool UserIsNotAdmin
        {
            get { return !LogedUserManager.IsAdministrator; }
        }
        
        [Inject] public IMatToaster Toaster { get; set; }
        public void ShowWarning(string title, string message)
        {
            Toaster.Add(message, MatToastType.Warning, title, "", config =>
            {
                config.ShowCloseButton = true;
                config.ShowProgressBar = true;
                config.MaximumOpacity = 90;
 
                config.ShowTransitionDuration = 500;
                config.VisibleStateDuration = 5000;
                config.HideTransitionDuration = 500;
 
                config.RequireInteraction = true;
                
            });
        }
        
        public void ShowError(string title, string message)
        {
            Toaster.Add(message, MatToastType.Danger, title, "", config =>
            {
                config.ShowCloseButton = true;
                config.ShowProgressBar = true;
                config.MaximumOpacity = 90;
 
                config.ShowTransitionDuration = 500;
                config.VisibleStateDuration = 5000;
                config.HideTransitionDuration = 500;
 
                config.RequireInteraction = true;
                
            });
        }
        
        public void ShowInfo(string title, string message)
        {
            Toaster.Add(message, MatToastType.Info, title, "", config =>
            {
                config.ShowCloseButton = true;
                config.ShowProgressBar = true;
                config.MaximumOpacity = 90;
 
                config.ShowTransitionDuration = 500;
                config.VisibleStateDuration = 5000;
                config.HideTransitionDuration = 500;
 
                config.RequireInteraction = true;
                
            });
        }
    }
}