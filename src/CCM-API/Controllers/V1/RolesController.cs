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
    [Authorize(Policy = "UserManagementRO")]
    [ApiVersion("1")] 
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class RolesController: BaseController<UserGroupsController>
    {
        public RolesController(ILogger<UserGroupsController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            RoleManager roleManager):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            this.roleManager = roleManager;
            ControllerName = "RolesController";
        }
        
        private readonly RoleManager roleManager;
        
        [HttpGet]
        public ActionResult<List<Role>> Get()
        {
            LogOperation(HttpOperationType.Get);
            var roles = roleManager.GetAll();

            if (roles == null) return NoContent();

            return roles;
        }
    }
}