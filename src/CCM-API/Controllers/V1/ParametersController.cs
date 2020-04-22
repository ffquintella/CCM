using System;
using System.Collections.Generic;
using Domain.Protocol;
using Domain.Security;
using Domain.System;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;

namespace CCM_API.Controllers
{
    [ApiController]
    [Authorize(Policy = "RequireAllAccess")]
    [ApiVersion("1")] 
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class ParametersController :  BaseController<ParametersController>
    {
        
        public ParametersController(ILogger<ParametersController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            SystemManager systemManager):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            this.systemManager = systemManager;
            ControllerName = "ParametersController";
        }
        
        private readonly SystemManager systemManager;
        
        [HttpGet]
        public ConsolidatedSystemParameters Get()
        {
            return systemManager.GetConsolidated();
        }
        
        [HttpGet("PasswordComplexity")]
        public PasswordComplexity GetPwdComplexity()
        {
            return systemManager.GetSystemPasswordComplexity();
        }
        
        [HttpPut("PasswordComplexity")]
        public ObjectOperationResponse SetPwdComplexity([FromBody] PasswordComplexity passwordComplexity)
        {
            var data = new Dictionary<string, string>();
            data.Add("pwd_MinSize", passwordComplexity.MinSize.ToString());
            data.Add("pwd_Letters", passwordComplexity.MustContainLetters.ToString());
            data.Add("pwd_Numbers", passwordComplexity.MustContainNumbers.ToString());
            data.Add("pwd_Symbols", passwordComplexity.MustContainSymbols.ToString());
            data.Add("pwd_CapLetters", passwordComplexity.MustContainSymbols.ToString());
            LogOperation(HttpOperationType.Put, data);

            var result = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error,
                Message = "Unkown Error"
            };

            try
            {
                systemManager.SetSystemPasswordComplexity(passwordComplexity);
                result.Status = ObjectOperationStatus.Updated;
                result.Message = "";
            }
            catch (Exception ex)
            {
                result.Message = ex.Message;
            }

            return result;
        }
        
    }
}