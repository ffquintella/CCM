using System.Collections.Generic;
using System.Net;
using Apache.Ignite.Core.Cache;
using Domain;
using Domain.Protocol;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;

namespace CCM_API.Controllers
{
    [ApiController]
    [Authorize(Policy = "UserManagement")]
    [ApiVersion("1")] 
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class UsersController : BaseController<UsersController>
    {
        public UsersController(ILogger<UsersController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            UserManager userManager):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            this.userManager = userManager;
        }

        private readonly UserManager userManager;
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpGet]
        public List<User> Get()
        {
            LogOperation(HttpOperationType.Get);
            
            var users = userManager.GetAllActiveUsers();

            return users;
        }
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpPost("listGet")]
        public List<User> ListGet([FromBody] List<long> idList)
        {
            var prm = new Dictionary<string, string>();
            prm.Add("Type", "ListGet");
            LogOperation(HttpOperationType.Post, prm);
            
            var users = userManager.GetUsersInList(idList);

            return users;
        }
        
        [HttpDelete("{id}")]
        public ActionResult<ObjectOperationResponse> Delete(long id)
        {
            LogOperation(HttpOperationType.Delete, id);
            
            var result = userManager.DeleteById(id);
            if (result.Status == ObjectOperationStatus.Deleted)
            {
                return Accepted(result);
            }

            return NotFound();
            
        }
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpGet("{id}")]
        public ActionResult<User> GetWithId(long id)
        {
            LogOperation(HttpOperationType.Get, id);
            var user = userManager.FindUserByUserId(id);

            if (user == null) return NotFound();
            
            return user;
        }
        
        // POST
        [HttpPost]
        public ActionResult<ObjectOperationResponse> Post([FromBody]User user)
        {
            var data = new Dictionary<string, string>();
            data.Add("name", user.Name);
            data.Add("email",user.Email);
            LogOperation(HttpOperationType.Post, data);
            
            if (user.Id != -1)
            {
                return BadRequest("Cannot set id for new user");
            }

            var result = userManager.Create(user);
            if (result.Status == ObjectOperationStatus.Created)
            {
                return Created("/Users/" + result.IdRef, result);
            }

            return StatusCode(500, result);
        }
        
        
        // PUT
        [HttpPut("{id}")]
        public ObjectOperationResponse Put(int id, [FromBody]User user)
        {
            LogOperation(HttpOperationType.Put, id);
            user.Id = id;
            return userManager.UpdateUser(user);
        }
        
    }
}