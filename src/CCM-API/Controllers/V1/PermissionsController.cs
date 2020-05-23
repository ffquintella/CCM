using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;

namespace CCM_API.Controllers
{
    [ApiController]
    [Authorize(Policy = "BasicAuth")]
    [ApiVersion("1")] 
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class PermissionsController: BaseController<ApplicationsController>
    {
        public PermissionsController(ILogger<ApplicationsController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            PermissionManager permManager):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            ControllerName = "PermissionsController";
            this.permManager = permManager;

        }

        private PermissionManager permManager;
    }
}