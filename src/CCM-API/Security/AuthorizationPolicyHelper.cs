using System.Security.Claims;
using Microsoft.AspNetCore.Authorization;
using Microsoft.Extensions.Options;

namespace CCM_API.Security
{
    public static class AuthorizationPolicyHelper
    {
        public static void ConfigureOptions(ref AuthorizationOptions options)
        {
            options.AddPolicy(
                "RequireAllAccess",
                policyBuilder =>
                {
                    policyBuilder.RequireAssertion(
                        context => context.User.HasClaim(claim =>
                            (claim.Type == ClaimTypes.AuthorizationDecision && claim.Value == "AllAccess")
                            || (claim.Type == ClaimTypes.Role && claim.Value == "Administrator")
                        )
                    );
                    policyBuilder.AuthenticationSchemes.Add("Basic");
                    policyBuilder.AuthenticationSchemes.Add("Token");
                    policyBuilder.RequireAuthenticatedUser();
                }

            );
            options.AddPolicy(
                "DataManagement",
                policyBuilder =>
                {
                    policyBuilder.RequireAssertion(
                        context => context.User.HasClaim(claim =>
                            (claim.Type == ClaimTypes.AuthorizationDecision && claim.Value == "AllAccess")
                            || (claim.Type == ClaimTypes.Role && claim.Value == "Administrator")
                        )
                    );
                    policyBuilder.AuthenticationSchemes.Add("Basic");
                    policyBuilder.AuthenticationSchemes.Add("Token");
                    policyBuilder.RequireAuthenticatedUser();
                });
            options.AddPolicy(
                "UserManagement",
                policyBuilder =>
                {
                    policyBuilder.RequireAssertion(
                        context => context.User.HasClaim(claim =>
                            (claim.Type == ClaimTypes.AuthorizationDecision && claim.Value == "AllAccess")
                            || (claim.Type == ClaimTypes.Role && claim.Value == "Administrator")
                            || (claim.Type == ClaimTypes.AuthorizationDecision &&
                                claim.Value == "WriteUsersClaim")
                        )
                    );
                    policyBuilder.AuthenticationSchemes.Add("Basic");
                    policyBuilder.AuthenticationSchemes.Add("Token");
                    policyBuilder.RequireAuthenticatedUser();
                });
            options.AddPolicy(
                "UserManagementRO",
                policyBuilder =>
                {
                    policyBuilder.RequireAssertion(
                        context => context.User.HasClaim(claim =>
                            (claim.Type == ClaimTypes.AuthorizationDecision && claim.Value == "AllAccess")
                            || (claim.Type == ClaimTypes.Role && claim.Value == "Administrator")
                            || (claim.Type == ClaimTypes.AuthorizationDecision &&
                                claim.Value == "ReadUsersClaim")
                        )
                    );
                    policyBuilder.AuthenticationSchemes.Add("Basic");
                    policyBuilder.AuthenticationSchemes.Add("Token");
                    policyBuilder.RequireAuthenticatedUser();
                }); 
            
            options.AddPolicy(
                "ApplicationsUser",
                policyBuilder =>
                {
                    policyBuilder.RequireAssertion(
                        context => context.User.HasClaim(claim =>
                            (claim.Type == ClaimTypes.AuthorizationDecision && claim.Value == "AllAccess")
                            || (claim.Type == ClaimTypes.Role && claim.Value == "User")
                            )
                        );
                    policyBuilder.AuthenticationSchemes.Add("Basic");
                    policyBuilder.AuthenticationSchemes.Add("Token");
                    policyBuilder.RequireAuthenticatedUser();
                }); 
            
            options.AddPolicy(
                "BasicAuth",
                policyBuilder =>
                {
                    policyBuilder.AuthenticationSchemes.Add("Basic");
                    policyBuilder.AuthenticationSchemes.Add("Token");
                    policyBuilder.RequireAuthenticatedUser();
                }

            );
        }
    }
}