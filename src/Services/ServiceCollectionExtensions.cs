using Microsoft.Extensions.DependencyInjection;
using Services.Authentication;
using Services.Security;
using Services.System;

namespace Services
{
    public static class ServiceCollectionExtensions
    {
        public static IServiceCollection AddCCMServices(this IServiceCollection services)
        {
            return services.AddSingleton<LoginService>()
                .AddSingleton<ConfigurationManager>()
                .AddSingleton<ParameterService>()
                .AddScoped<UserService>()
                .AddScoped<UserGroupService>()
                .AddScoped<AccountService>()
                .AddScoped<RoleService>()
                .AddScoped<RoleManager>()
                .AddScoped<LogedUserManager>();
        }
    }
}