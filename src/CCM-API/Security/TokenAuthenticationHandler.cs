using System.Collections.Generic;
using System.Net.Http;
using System.Security.Claims;
using System.Text.Encodings.Web;
using System.Threading.Tasks;
using CCM_API.Helpers;
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
    public class TokenAuthenticationHandler: AuthenticationHandler<AuthenticationSchemeOptions>
    {
        protected static readonly ILogger logger = Log.Logger;

        protected IConfiguration Configuration { get; set; }
        public TokenAuthenticationHandler(
            IOptionsMonitor<AuthenticationSchemeOptions> options,
            ILoggerFactory logger,
            UrlEncoder encoder,
            IConfiguration configuration,
            ISystemClock clock,
            AuthenticationManager authenticationManager)
            : base(options, logger, encoder, clock)
        {
            Configuration = configuration;
            this.authenticationManager = authenticationManager;
        }
        
        private readonly AuthenticationManager authenticationManager;
        
        protected override Task<AuthenticateResult> HandleAuthenticateAsync()
        {
            
            logger.Debug("Computing token api authentication");

            if (Request.Headers.ContainsKey("AuthenticationToken"))
            {
                return TokenAuthentication();
            }

            return Task.FromResult(AuthenticateResult.Fail("Missing Authorization Header"));
            
            
        }
        
        protected Task<AuthenticateResult> TokenAuthentication()
        {
            string token = Request.Headers["AuthenticationToken"];

            var authResult = authenticationManager.ValidateTokenWithData(token, Request.HttpContext.Connection.RemoteIpAddress);
            if (authResult.Item1)
            {
                var authData = authResult.Item2;

                var claims = authData.Claims;
                claims.Add(new Claim(ClaimTypes.UserData, authData.Token, ClaimValueTypes.String, "https://ccm.domain"));
                
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