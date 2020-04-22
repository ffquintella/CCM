using System.Linq;
using System.Security.Claims;
using Domain.Authentication;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;

namespace CCM_API.Controllers
{
    [ApiController]
    [Authorize]
    [ApiVersion("1")] 
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class AuthenticationController : BaseController<AuthenticationController>
    {
        public AuthenticationController(
            ILogger<AuthenticationController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager, 
            IHttpContextAccessor httpContextAccessor,
            AuthenticationManager authenticationManager): base(logger,configuration,igniteManager, httpContextAccessor)
        {
            this.authenticationManager = authenticationManager;
            ControllerName = "AuthenticationController";
        }
        protected readonly AuthenticationManager authenticationManager;
        
        [HttpGet]
        public AuthenticationData Get()
        {
            var token = Request.HttpContext.User.Claims.FirstOrDefault(claim => claim.Type == ClaimTypes.UserData)
                .Value;
            
            var authData = authenticationManager.GetAuthenticationData(token);

            return authData;
        }
        [AllowAnonymous]
        [HttpPost("Authenticate")]
        public AuthenticationData Authenticate([FromBody] AuthenticationRequest request)
        {
            var authData = new AuthenticationData()
            {
                Status = AuthenticationStatus.NotLoggedIn,
                ErrorType = AuthenticationErrorType.NoError
            };

            if (request == null)
            {
                authData.Status = AuthenticationStatus.NotLoggedIn;
                authData.ErrorType = AuthenticationErrorType.RequestBadFormated;
                authData.ErrorMessage = "The request object cannot be null";
                return authData;
            }

            if (string.IsNullOrEmpty(request.Login))
            {
                authData.Status = AuthenticationStatus.NotLoggedIn;
                authData.ErrorType = AuthenticationErrorType.LoginDoesntExists;
                authData.ErrorMessage = "You must enter a login";
                return authData;
            }
            
            if (string.IsNullOrEmpty(request.Password))
            {
                authData.Status = AuthenticationStatus.NotLoggedIn;
                authData.ErrorType = AuthenticationErrorType.BadPassword;
                authData.ErrorMessage = "You must enter a password";
                return authData;
            }

            var authResult = authenticationManager.ExecuteAuthentication(request, HttpContext.Connection.RemoteIpAddress, request.Login);
            
            if (authResult.Item1 == AuthenticationErrorType.NoError)
            {
                authData = authResult.Item2;
                return authData;
            }
            
            if (authResult.Item1 == AuthenticationErrorType.BadPassword)
            {
                authData.Status = AuthenticationStatus.NotLoggedIn;
                authData.ErrorType = AuthenticationErrorType.BadPassword;
                authData.ErrorMessage = "You must enter a password";
                return authData;
            }
            
            if (authResult.Item1 == AuthenticationErrorType.LoginDoesntExists)
            {
                authData.Status = AuthenticationStatus.NotLoggedIn;
                authData.ErrorType = AuthenticationErrorType.LoginDoesntExists;
                authData.ErrorMessage = "You must enter a login";
                return authData;
            }
            
            
            return authData;
        }
        
        [AllowAnonymous]
        [HttpGet("ValidateToken")]
        public bool ValidateToken([FromQuery] string token = "")
        {

            return authenticationManager.ValidateToken(token, HttpContext.Connection.RemoteIpAddress);
            
        }
    }
}