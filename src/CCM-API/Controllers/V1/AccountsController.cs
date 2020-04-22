using System.Collections.Generic;
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
    public class AccountsController : BaseController<AccountsController>
    {
        public AccountsController(ILogger<AccountsController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            AccountManager accountManager):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            this.accountManager = accountManager;
            ControllerName = "AccountsController";
        }
        
        private readonly AccountManager accountManager;
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpGet]
        public List<Account> List()
        {
            var accounts = accountManager.GetAllActiveAccounts();
            return accounts;
        }
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpGet("fromUser")]
        public Account Get([FromQuery] int userId)
        {
            LogOperation(HttpOperationType.Get);
            var account = accountManager.FindByUserId(userId);
            return account;
        }
        
        [Authorize(Policy = "UserManagementRO")]
        [HttpGet("{id}")]
        public Account GetAccount(int id)
        {
            LogOperation(HttpOperationType.Get, id);
            var account = accountManager.FindById(id);
            return account;
        }
        

        [HttpDelete("{id}")]
        public ActionResult<ObjectOperationResponse> Delete(int id)
        {
            LogOperation(HttpOperationType.Delete, id);
            var result = accountManager.DeleteById(id);
            if (result.Status == ObjectOperationStatus.Deleted)
            {
                return Accepted(new ObjectOperationResponse()
                {
                    IdRef = id,
                    Status = ObjectOperationStatus.Deleted
                });
            }

            return NotFound();
            
        }
        
        [HttpPost]
        public ActionResult<ObjectOperationResponse> Post(Account account)
        {
            var data = new Dictionary<string, string>();
            data.Add("login", account.Login);
            LogOperation(HttpOperationType.Post, data);
            if (account.Id != -1)
            {
                return BadRequest("Cannot set id for new account");
            }
            var result = accountManager.Create(account);
            if (result.Status == ObjectOperationStatus.Created)
            {
                return Created("/Accounts/" + result.IdRef, result);
            }

            return StatusCode(500, result);
        }
        
        [HttpPut("{id}")]
        public ActionResult<ObjectOperationResponse> Put(long id, Account account)
        {
            LogOperation(HttpOperationType.Put, id);
            if (account.Id != id)
            {
                return BadRequest("Data inconsistent");
            }
            
            if (account.Id < 0 || id < 0 )
            {
                return BadRequest("Cannot update new account");
            }
            var result = accountManager.Update(account);
            if (result.Status == ObjectOperationStatus.Updated)
            {
                return Ok(result);
            }
            
            if (result.Status == ObjectOperationStatus.NotFound)
            {
                return NotFound();
            }

            return StatusCode(500, result);
        }
        
    }
}