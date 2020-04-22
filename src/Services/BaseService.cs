using Microsoft.AspNetCore.Components;
using Serilog;
using Services.Authentication;

namespace Services
{
    public class BaseService
    {
        [Inject] private LoginService LoginService { get; set; }
        protected readonly ILogger logger = Log.Logger;
    }
}