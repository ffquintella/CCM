using System;
using System.Collections.Generic;
using System.Data.Common;
using Domain.Protocol;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using Environment = Domain.Environment;

namespace CCM_API.Controllers
{
    
    [ApiController]
    [ApiVersion("1")] 
    [Authorize(Policy = "BasicAuth")]
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class EnvironmentsController: BaseController<EnvironmentsController>
    {
        public EnvironmentsController(ILogger<EnvironmentsController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            EnvironmentManager envManager
        ):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            ControllerName = "EnvironmentsController";
            this.envManager = envManager;
        }
        
        private EnvironmentManager envManager;
        
        // GET
        [HttpGet]
        public List<Environment> Get([FromQuery] bool getDisabled = false)
        {
            var result = envManager.GetAll(getDisabled);

            return result;
        }
        
        [HttpGet("{id}")]
        public ActionResult<Environment> Get(long id)
        {
            if (id < 0) return BadRequest("Id must be possitive");
            
            var result = envManager.FindById(id);

            if (result == null) return NotFound();
            
            return Ok(result);
        }

        [Authorize(Policy = "RequireAllAccess")]
        [HttpPost]
        public ActionResult<ObjectOperationResponse> Create([FromBody] Environment env)
        {
            if(env == null) return BadRequest();


            var result = envManager.Create(env);

            if (result.Status == ObjectOperationStatus.Created)
            {
                string url = string.Concat(this.Request.Scheme, "://", this.Request.Host, "/api/v1/Environments/" + env.Id );
                return Created(url, result);
            }
            
            return StatusCode(500, result);

        }
        
        [Authorize(Policy = "RequireAllAccess")]
        [HttpPut("{id}")]
        public ActionResult<ObjectOperationResponse> Update(long id, [FromBody] Environment env)
        {
            if (id < 0) return BadRequest("Id must be possitive");
            if(env == null) return BadRequest();

            var result = envManager.Update(id,env);

            if (result.Status == ObjectOperationStatus.Updated) return Ok(result);
            
            return StatusCode(500,result);

        }
        
        [Authorize(Policy = "RequireAllAccess")]
        [HttpDelete("{id}")]
        public ActionResult<ObjectOperationResponse> Delete(long id)
        {
            if (id < 0) return BadRequest("Id must be possitive");

            var result = envManager.Delete(id);

            if (result.Status == ObjectOperationStatus.Deleted) return Ok(result);
            return StatusCode(500, result);

        }
        
    }
}