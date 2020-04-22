using CCM_WebClient.Translation;
using Domain.Security;
using Services.System;
using Microsoft.AspNetCore.Components;

namespace CCM_WebClient.Models
{
    public class ParametersModel: BaseModel
    {
        
        public PasswordComplexity Complexity { get; set; }
        
        [Inject] private ParameterService ParameterService { get; set; }

        public ParametersModel()
        {
            //this.ParameterService = parameterService;
            //Complexity = new PasswordComplexity();
        }
        
        protected override void OnInitialized()
        {
            var consolidated = ParameterService.GetAllSystemParameters();
            Complexity = consolidated.PasswordComplexity;
        }

        public void SavePasswordSettings()
        {
            ParameterService.SaveSystemPasswordComplexity();
            StateHasChanged();
            ShowInfo(T._("Configurations saved"), T._("Password configurations saved"));
        }

        public void LoadPasswordSettings()
        {
            Complexity = ParameterService.GetSystemPasswordComplexity(true);
            StateHasChanged();
            ShowInfo(T._("Configurations restored"), T._("Password configurations restored"));
        }
        
        
    }
}