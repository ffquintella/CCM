using System.Collections.Generic;
using System.Security.Claims;
using System.Text.Encodings.Web;
using System.Threading.Tasks;
using Domain;
using Microsoft.AspNetCore.Authentication;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;
using Serilog;
using Serilog.Core;
using ILogger = Serilog.ILogger;

namespace CCM_API.Security
{
    public class BasicAuthenticationHandler: AuthenticationHandler<AuthenticationSchemeOptions>
    {
        protected static readonly ILogger logger = Log.Logger;

        protected IConfiguration Configuration { get; set; }
        public BasicAuthenticationHandler(
            IOptionsMonitor<AuthenticationSchemeOptions> options,
            ILoggerFactory logger,
            UrlEncoder encoder,
            IConfiguration configuration,
            ISystemClock clock,
            AccountManager accountManager,
            UserManager userManager,
            ClaimManager claimManager)
            : base(options, logger, encoder, clock)
        {
            Configuration = configuration;
            this.accountManager = accountManager;
            this.userManager = userManager;
            this.claimManager = claimManager;
            
        }
        
        private readonly AccountManager accountManager;
        private readonly UserManager userManager;
        
        private readonly ClaimManager claimManager;
        
        protected override Task<AuthenticateResult> HandleAuthenticateAsync()
        {
            
            logger.Debug("Computing basic api authentication");

            if (Request.Headers.ContainsKey("Authorization"))
            {
                return BasicAuthentication();
            }

            return Task.FromResult(AuthenticateResult.Fail("Missing Authorization Header"));
            
            
        }
        
        protected Task<AuthenticateResult> BasicAuthentication()
        {
            string authorization = Request.Headers["Authorization"];

            var valid_request = false;
            Account account = null;

            if (authorization.StartsWith("Basic"))
            {
                string base64Encoded = authorization.Split(" ")[1].Trim();
                string userpwd;
                byte[] data = System.Convert.FromBase64String(base64Encoded);
                userpwd = System.Text.Encoding.UTF8.GetString(data);
                var login = userpwd.Split(":")[0];
                var password = userpwd.Split(":")[1];
                
                // Find account
                account = accountManager.FindByLogin(login);

                // Not Found
                if (account != null)
                {
                    if (PasswordTool.VerifyHashedPassword(account.GetPassword(), password))
                    {
                        valid_request = true;
                    }
                }

            }

            if (valid_request)
            {
                var user = userManager.FindUserByAccountId(account.Id);

                var claims = claimManager.GetUserClaims(user);
                
                var identity = new ClaimsIdentity(claims, Scheme.Name);
                var principal = new ClaimsPrincipal(identity);
                var ticket = new AuthenticationTicket(principal, Scheme.Name);
                
                return Task.FromResult(AuthenticateResult.Success(ticket));
            }
            else
            {
                return Task.FromResult(AuthenticateResult.Fail("Invalid authentication credentials"));
            }
        }
        
    }
}