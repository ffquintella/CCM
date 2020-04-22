using System.Collections.Generic;
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
    public class CCMController: BaseController<CCMController>
    {
        public CCMController(ILogger<CCMController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            CCMManager ccmManager):
            base(logger,configuration,igniteManager,httpContextAccessor)
        {
            this.ccmManager = ccmManager;
            ControllerName = "CCMController";
        }
        
        private readonly CCMManager ccmManager;
        
        [HttpGet]
        public string[] Get([FromQuery] int testUsers = 0)
        {
            var result = new string[]
            {
                "Bootstrap",
            };
            return result;
        }
        
        // GET Bootstrap
        [AllowAnonymous]
        [HttpGet("Bootstrap")]
        public string BootStrap([FromQuery] int testUsers = 0)
        {
            var data = new Dictionary<string, string>();
            data.Add("Bootstrap", "true");
            data.Add("TestUsers", testUsers.ToString());

            LogOperation(HttpOperationType.Get, data);
            if (Configuration["app:allowBootstrap"] == "true")
            {
                ccmManager.Bootstrap(testUsers);
                return "Bootstrap finished";
            }
            else
            {
                return "Bootstrap disabled";
            }
        }
    }
}