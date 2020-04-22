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
    [Authorize(Policy = "UserManagement")]
    [ApiVersion("1")] 
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class UserGroupsController: BaseController<UserGroupsController>
    {
        public UserGroupsController(ILogger<UserGroupsController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            UserGroupManager userGroupManager):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            this.userGroupManager = userGroupManager;
            ControllerName = "UserGroupsController";
        }
        private readonly UserGroupManager userGroupManager;
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpGet]
        public ActionResult<List<UserGroup>> Get()
        {
            LogOperation(HttpOperationType.Get);
            var users = userGroupManager.GetAll();

            if (users == null) return NoContent();

            return users;
        }
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpGet("findOne")]
        public ActionResult<UserGroup> FindOne([FromQuery] string name)
        {
            var user = userGroupManager.FindOne(name);

            if (user == null) return NoContent();

            return user;
        }
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpGet("{id}")]
        public ActionResult<UserGroup> GetItem(long id)
        {
            LogOperation(HttpOperationType.Get, id);
            var user = userGroupManager.Get(id);

            if (user == null) return NotFound();

            return user;
        }
        
        [HttpDelete("{id}")]
        public ActionResult<ObjectOperationResponse> DeleteItem(long id)
        {
            LogOperation(HttpOperationType.Delete, id);
            var response = userGroupManager.DeleteById(id);

            if (response.Status == ObjectOperationStatus.NotFound) return NotFound(response);
            if (response.Status == ObjectOperationStatus.Deleted) return Accepted(response);

            return StatusCode(500, response);
        }
        
        
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpGet("{id}/exists")]
        public ActionResult Exists(long id)
        {
            if (userGroupManager.Exists(id)) return Ok();
            return NotFound();
        }
        
        // POST - Create
        [HttpPost]
        public ActionResult<ObjectOperationResponse> Post([FromBody]UserGroup group)
        {
            var data = new Dictionary<string, string>();
            data.Add("name", group.Name);
            LogOperation(HttpOperationType.Post, data);

            if (group.Id > 0 )
            {
                return BadRequest("Cannot set id for new user");
            }

            var result = userGroupManager.Create(group);
            if (result.Status == ObjectOperationStatus.Created)
            {
                return Created("/UserGroups/" + result.IdRef, result);
            }

            return StatusCode(500, result);
        }
        
        // PUT - Update
        [HttpPut("{id}")]
        public ActionResult<ObjectOperationResponse> Put(long id, [FromBody]UserGroup group)
        {
            LogOperation(HttpOperationType.Put, id);
            if (id < 0 )
            {
                return BadRequest("Cannot update a new user");
            }

            //let's check if the is exists

            if (userGroupManager.Exists(id))
            {
                // ok then we will update
                var result = userGroupManager.Update(group);
                if (result.Status == ObjectOperationStatus.Updated)
                {
                    return Accepted("/UserGroups/" + result.IdRef, result);
                }

                return StatusCode(500, result);
            }
            else
            {
                //not found 
                return NotFound(new ObjectOperationResponse()
                {
                    IdRef = id,
                    Message = "Id not found",
                    Status = ObjectOperationStatus.NotFound
                });
            }

            return StatusCode(500, new ObjectOperationResponse()
            {
                IdRef = id,
                Message = "Uknown error",
                Status = ObjectOperationStatus.Error
            });
        }
        
    }
}