using System;
using System.Collections.Generic;
using System.Security.Claims;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;

namespace CCM_API.Controllers
{
    public class BaseController<T> : ControllerBase
    {
        public BaseController(ILogger<T> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor)
        {
            Configuration = configuration;
            Logger = logger;
            IgniteManager = igniteManager;
            HttpContextAccessor = httpContextAccessor;
            ControllerName = typeof(T).Name;
        }

        protected string ControllerName { get; set; }
        protected IHttpContextAccessor HttpContextAccessor { get; }
        protected IConfiguration Configuration { get; }
        protected  ILogger<T> Logger { get;  }
        protected  IgniteManager IgniteManager { get; }

        protected string GetLoggedUserName()
        {
            try
            {
                var userName = HttpContextAccessor.HttpContext.User.FindFirst(ClaimTypes.Name).Value;
                return userName;
            }
            catch (Exception ex)
            {
                return "";
            }
        }

        private string GetOperationName(HttpOperationType operationType)
        {
            string operName = "NOTDEF";
            switch (operationType)
            {
                case HttpOperationType.Delete:
                    operName = "DELETE";
                    break;
                case HttpOperationType.Get:
                    operName = "GET";
                    break;
                case HttpOperationType.Post:
                    operName = "POST";
                    break;
                case HttpOperationType.Put:
                    operName = "PUT";
                    break;
                case HttpOperationType.Patch:
                    operName = "PATCH";
                    break;
            }

            return operName;
        }

        protected void LogOperation(HttpOperationType operationType, Dictionary<string, string> parameters)
        {
            string operName = GetOperationName(operationType);
            string paramStr = "";

            foreach (var key in parameters.Keys)
            {
                var value = parameters[key];
                paramStr += string.Format(" {0}:{1} ", key, value);
            }
            Logger.LogInformation("Controller:{controller} Operation:{oper} executed by user:{user} with data {data}", 
                ControllerName,
                operName, 
                GetLoggedUserName(),
                paramStr);
        }

        protected void LogOperation(HttpOperationType operationType, long id = -1)
        {
            string operName = GetOperationName(operationType);
            if(id == -1 ) Logger.LogInformation("Controller:{controller} Operation:{oper} executed by user:{user}", 
                ControllerName, operName, GetLoggedUserName());
            else Logger.LogInformation("Controller:{controller} Operation:{oper} executed by user:{user} object id={id}",
                ControllerName,
                operName, 
                GetLoggedUserName(), 
                id);
        }
        
    }
}