using System;
using System.Collections.Generic;
using System.Linq;
using System.Security.Claims;
using System.Threading.Tasks;
using CCM_API.Security;
using Microsoft.AspNetCore.Builder;
using Microsoft.AspNetCore.Hosting;
using Microsoft.AspNetCore.HttpsPolicy;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using Microsoft.OpenApi.Models;

namespace CCM_API
{
    public class Startup
    {
        public Startup(IConfiguration configuration)
        {
            Configuration = configuration;

        }

        public IConfiguration Configuration { get; }

        private IgniteManager imanager;

        // This method gets called by the runtime. Use this method to add services to the container.
        public void ConfigureServices(IServiceCollection services)
        {

            imanager = new IgniteManager(Configuration);
            imanager.StartIgnite();

            services.AddControllers();
            
            services.AddApiVersioning(p =>
            {
                p.DefaultApiVersion = new ApiVersion(1, 0);
                p.ReportApiVersions = true;
                p.AssumeDefaultVersionWhenUnspecified = true;
            });

            services.AddVersionedApiExplorer(p =>
            {
                p.GroupNameFormat = "'v'VVV";
                p.SubstituteApiVersionInUrl = true;
            });

            services.AddSwaggerGen(c =>
            {
                c.SwaggerDoc("v1", new OpenApiInfo{ Title =  "CCM API", Version = "v1"} );
            });
            
            services.AddAuthorization(options =>
                {
                    var defaultAuthorizationPolicyBuilder = new AuthorizationPolicyBuilder(
                        "Basic", "Token");
                    defaultAuthorizationPolicyBuilder = 
                        defaultAuthorizationPolicyBuilder.RequireAuthenticatedUser();
                    options.DefaultPolicy = defaultAuthorizationPolicyBuilder.Build();
                    
                    
                    AuthorizationPolicyHelper.ConfigureOptions(ref options);
                    
                    
                }
            );
            
            
            services.AddSingleton<IgniteManager>(imanager);
            services.AddSingleton<UserManager>();
            services.AddSingleton<UserGroupManager>();
            services.AddSingleton<AccountManager>();
            services.AddSingleton<AuthenticationManager>();
            services.AddSingleton<ClaimManager>();
            services.AddSingleton<CCMManager>();
            services.AddSingleton<RoleManager>();
            services.AddSingleton<DataManager>();
            services.AddSingleton<EnvironmentManager>();
            services.AddSingleton<FileManager>();
            services.AddSingleton<SystemManager>();
            services.AddSingleton<IHttpContextAccessor, HttpContextAccessor>();
            
            // configure basic authentication 
            services.AddAuthentication("Basic")
                .AddScheme<AuthenticationSchemeOptions, Security.BasicAuthenticationHandler>("Basic", null)
                .AddScheme<AuthenticationSchemeOptions, Security.TokenAuthenticationHandler>("Token", null);
            
        }

        // This method gets called by the runtime. Use this method to configure the HTTP request pipeline.
        public void Configure(IApplicationBuilder app, IWebHostEnvironment env,IHostApplicationLifetime applicationLifetime)
        {
            if (env.IsDevelopment())
            {
                app.UseDeveloperExceptionPage();
            }

            app.UseSwagger();

            app.UseSwaggerUI(c =>
            {
                c.SwaggerEndpoint("/swagger/v1/swagger.json", "CCM API V1");
            });
            
            applicationLifetime.ApplicationStopping.Register(OnShutdown);

            app.UseHttpsRedirection();
            
            app.UseApiVersioning();
            app.UseRouting();

            app.UseAuthentication();
            app.UseAuthorization();

            app.UseEndpoints(endpoints => { endpoints.MapControllers(); });
        }
        private void OnShutdown()
        {
            //this code is called when the application stops
            imanager.StopIgnite();
        }
    }
}