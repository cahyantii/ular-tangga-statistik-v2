
export const state = {
    myGamePlayerId: null,
    latestSession: null,
    knownPositions: new Map(),
    questionCountdownInterval: null,
    duelCountdownInterval: null,
    pausedCountdownInterval: null,
    heartbeatIntervalId: null,
    presenceChannel: null,
    leaveConfirmed: false,
    diceRotation: { x: 0, y: 0 },
    animationChain: Promise.resolve()
};

export const dom = {
    root: null, boardEl: null, pawnLayer: null, rollButton: null, diceCube: null, diceGlowWrap: null,
    turnAvatarEl: null, turnIndicatorEl: null, turnSubtextEl: null, toastEl: null,
    pausedBannerEl: null, pausedTimerEl: null, playerPanelListEl: null, logListEl: null,
    logEmptyEl: null, questionTextEl: null, questionOptionsEl: null, questionTimerEl: null,
    questionFeedbackEl: null, duelStatusTextEl: null, duelQuestionNumberEl: null,
    duelTextEl: null, duelOptionsEl: null, duelTimerEl: null, duelFeedbackEl: null,
    playerCardTemplate: null, robotCardTemplate: null
};

export const config = {
    stateUrl: null, rollUrl: null, answerUrl: null, leaveUrl: null, heartbeatUrl: null,
    currentUserId: null, jumlahPetak: null, csrfToken: null, jumlahKolom: null, 
    totalRows: null, konektorByStart: null
};

export function initState(elements, configuraton) {
    Object.assign(dom, elements);
    Object.assign(config, configuraton);
}
