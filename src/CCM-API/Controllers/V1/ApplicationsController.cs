using System.Collections.Generic;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using Domain;
using Domain.Protocol;
using Microsoft.AspNetCore.Http;

namespace CCM_API.Controllers
{
    
    [ApiController]
    [Authorize(Policy = "ApplicationsUser")]
    [ApiVersion("1")] 
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class ApplicationsController: BaseController<ApplicationsController>
    {
        public ApplicationsController(ILogger<ApplicationsController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            ApplicationManager appManager):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            ControllerName = "ApplicationsController";
            this.appManager = appManager;
        }

        private ApplicationManager appManager;



    }
}