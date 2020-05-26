using System;
using System.Collections.Generic;
using System.Linq;
using System.Security.Claims;
using CCM_API.Exceptions;
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
            ApplicationManager appManager,
            PermissionManager permManager):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            ControllerName = "ApplicationsController";
            this.appManager = appManager;
            this.permManager = permManager;

        }

        private ApplicationManager appManager;
        private PermissionManager permManager;
        

        [HttpGet]
        public ActionResult<List<Application>> Get()
        {
            LogOperation(HttpOperationType.Get);
            var apps = appManager.GetUserApps(GetLoggedUserAccountId());

            if (apps == null) return NoContent();

            return apps;

            return NotFound();
        }


        [HttpGet("{id}/permissions")]
        public ActionResult<List<Permission>> GetPermissions(long id)
        {
            LogOperation(HttpOperationType.Get);
            try
            {
                if (!permManager.ValidateUserObjectPermission(GetLoggedUserId(), id, PermissionType.Application,
                    PermissionConsent.Read))
                {
                    Logger.LogWarning("Unauthorized attempt to userid:{0} on app:{1}", GetLoggedUserId(), id);
                    return Unauthorized();
                }
                
                var perms = permManager.GetOwnerPermissions(id, PermissionType.Application);
                return perms;
            }
            catch (Exception ex)
            {
                Logger.LogError(ex.Message);
                return StatusCode(500);
            }
        }

        [HttpGet("{id}")]
        public ActionResult<Application> GetOne(long id)
        {
            LogOperation(HttpOperationType.Get);
            try
            {
                var app = appManager.GetUserApp(GetLoggedUserAccountId(), id);
                if (app == null) return NotFound();

                return app;
            }
            catch (NoPermissionException nop)
            {
                return Unauthorized();
            }
            catch (Exception ex)
            {
                return BadRequest();
            }



        }       
        

    }
}